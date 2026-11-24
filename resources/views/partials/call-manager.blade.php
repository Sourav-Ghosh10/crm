<!-- ══════════════════════════════════════════════════════════════ -->
<!-- CALL MANAGER CONTROLLER (ALPINE & WEBRTC LOGIC)               -->
<!-- Defined at top so Alpine finds it immediately upon parsing    -->
<!-- ══════════════════════════════════════════════════════════════ -->
<script>
function callManagerComponent() {
    return {
        currentUserId: {{ auth()->id() ?? 'null' }},
        currentUserName: "{{ addslashes(auth()->user()->name ?? '') }}",
        callState: 'idle', // 'idle', 'calling', 'ringing', 'connected'
        callType: 'audio', // 'audio' or 'video'
        roomId: null,
        targetUserId: null,
        callerId: null,
        callerName: '',
        remoteUserName: '',
        isMinimized: false,
        isMuted: false,
        isCameraOn: true,
        isScreenSharing: false,
        isAccepting: false,
        remoteHasVideo: false,
        mediaConnected: false,
        screenSender: null,
        durationSeconds: 0,
        timerInterval: null,
        callTimeout: null,
        audioContext: null,
        ringtoneInterval: null,
        peerConnection: null,
        localStream: null,
        remoteStream: null,
        screenStream: null,
        iceCandidateQueue: [],
        processedSignalIds: new Set(),

        rtcConfig: {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' },
                { urls: 'stun:stun2.l.google.com:19302' },
                { urls: 'stun:stun3.l.google.com:19302' },
                { urls: 'stun:stun4.l.google.com:19302' },
                {
                    urls: 'turn:openrelay.metered.ca:80',
                    username: 'openrelayproject',
                    credential: 'openrelayproject'
                },
                {
                    urls: 'turn:openrelay.metered.ca:443',
                    username: 'openrelayproject',
                    credential: 'openrelayproject'
                },
                {
                    urls: 'turn:openrelay.metered.ca:443?transport=tcp',
                    username: 'openrelayproject',
                    credential: 'openrelayproject'
                }
            ],
            iceCandidatePoolSize: 10
        },

        get formattedDuration() {
            const mins = Math.floor(this.durationSeconds / 60);
            const secs = this.durationSeconds % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },

        init() {
            if (!this.currentUserId) return;
            console.log('[WebRTC CallManager] Initialized for user:', this.currentUserId, '(' + this.currentUserName + ')');

            this.processedSignalIds = new Set();

            // 1. Listen for signals forwarded from custom events
            window.addEventListener('call-signal-received', (event) => {
                console.log('[WebRTC CallManager] Received signal via window CustomEvent:', event.detail);
                this.handleIncomingSignal(event.detail);
            });

            // 2. Attach Echo signaling listener directly to target user's private channel
            this.waitForEcho(() => {
                const userChannelName = `App.Models.User.${this.currentUserId}`;
                const userChannel = window.Echo.private(userChannelName);
                this.bindSignalListeners(userChannel, userChannelName);
            });

            // Make available globally for testing / debugging
            window.__callManager = this;
            window.__testIncomingCall = (callerName = 'Admin', callType = 'audio') => {
                this.callerId = 1;
                this.callerName = callerName;
                this.remoteUserName = callerName;
                this.callType = callType;
                this.roomId = null;
                this.callState = 'ringing';
                this.startRingTone('incoming');
                console.log('[WebRTC CallManager] Test incoming call triggered!');
            };
        },

        bindSignalListeners(channel, channelName) {
            console.log('[WebRTC CallManager] Subscribing to CallSignal on channel:', channelName);

            const handler = (data) => {
                console.log(`[WebRTC CallManager] Received CallSignal on [${channelName}]:`, data);
                this.handleIncomingSignal(data);
            };

            channel.listen('.CallSignal', handler);
        },

        waitForEcho(callback, attempts = 0) {
            if (window.Echo) {
                callback();
            } else if (attempts < 25) {
                setTimeout(() => this.waitForEcho(callback, attempts + 1), 400);
            } else {
                console.warn('[WebRTC CallManager] Echo not detected after 10s.');
            }
        },

        async getLocalUserMedia() {
            const audioConstraints = {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true
            };
            try {
                return await navigator.mediaDevices.getUserMedia({
                    audio: audioConstraints,
                    video: this.callType === 'video' ? { width: { ideal: 1280 }, height: { ideal: 720 } } : false
                });
            } catch (err) {
                console.warn('[WebRTC CallManager] Advanced audio constraints failed, trying basic fallback:', err);
                try {
                    return await navigator.mediaDevices.getUserMedia({
                        audio: true,
                        video: this.callType === 'video' ? true : false
                    });
                } catch (err2) {
                    if (this.callType === 'video') {
                        console.warn('[WebRTC CallManager] Camera unavailable, falling back to audio-only:', err2);
                        this.callType = 'audio';
                        return await navigator.mediaDevices.getUserMedia({ audio: true });
                    }
                    throw err2;
                }
            }
        },

        // ══════════════════════════════════════════════════════════════
        // CALL INITIATION (CALLER SIDE)
        // ══════════════════════════════════════════════════════════════
        async startCall(detail) {
            console.log('[WebRTC CallManager] startCall triggered:', detail);
            if (this.callState !== 'idle') {
                alert('You are already on an active call.');
                return;
            }

            // Unlock remoteAudio element on user interaction gesture
            const remoteAudio = document.getElementById('remoteAudio');
            if (remoteAudio) {
                remoteAudio.play().catch(() => {});
            }

            this.targetUserId = detail.targetUserId;
            this.remoteUserName = detail.targetUserName || 'User';
            this.callType = detail.callType || 'audio';
            this.roomId = detail.roomId || null;
            this.callState = 'calling';
            this.isMinimized = false;
            this.durationSeconds = 0;

            try {
                // Request user media with echo cancellation and noise suppression
                this.localStream = await this.getLocalUserMedia();

                this.attachLocalVideo();
                this.startRingTone('outgoing');

                // Send call request signal to target user
                await this.sendSignal('incoming-call', {
                    callType: this.callType,
                    roomId: this.roomId,
                });

                // Auto-timeout after 40 seconds if no answer
                this.callTimeout = setTimeout(() => {
                    if (this.callState === 'calling') {
                        this.logCallStatus('missed');
                        this.endCall(true);
                        alert(`${this.remoteUserName} is not answering.`);
                    }
                }, 40000);

            } catch (err) {
                console.error('[WebRTC CallManager] Error accessing media devices:', err);
                alert('Microphone / Camera access was denied. Please allow permissions in your browser to make calls.');
                this.resetCallState();
            }
        },

        // ══════════════════════════════════════════════════════════════
        // INCOMING SIGNALS (PUSHER)
        // ══════════════════════════════════════════════════════════════
        async handleIncomingSignal(raw) {
            if (!raw) return;

            let data = raw;
            if (typeof data === 'string') {
                try { data = JSON.parse(data); } catch(e) {}
            }
            if (data && data.data) {
                if (typeof data.data === 'string') {
                    try { Object.assign(data, JSON.parse(data.data)); } catch(e) {}
                } else if (typeof data.data === 'object') {
                    Object.assign(data, data.data);
                }
            }

            // Ignore signals sent by myself
            if (data.senderId && parseInt(data.senderId) === parseInt(this.currentUserId)) {
                return;
            }

            // If signal targets a specific user and it is not me, ignore
            if (data.targetUserId && parseInt(data.targetUserId) !== parseInt(this.currentUserId)) {
                return;
            }

            const type = data.type;
            const senderId = data.senderId;
            const payload = data.payload || {};

            // Robust deduplication: prevent candidate collisions while dropping true duplicate signals
            let signalId = data.signalId;
            if (!signalId) {
                if (type === 'webrtc-ice-candidate' && payload && payload.candidate) {
                    const c = payload.candidate;
                    const cStr = typeof c === 'string' ? c : (c.candidate || '');
                    const mid = c.sdpMid || '';
                    const mLine = c.sdpMLineIndex ?? '';
                    signalId = `ice_${senderId}_${cStr}_${mid}_${mLine}`;
                } else if (type === 'incoming-call') {
                    signalId = `call_${senderId}_${data.roomId || ''}_${data.callType || payload.callType || ''}`;
                } else if (['call-accepted', 'call-declined', 'call-busy', 'call-ended'].includes(type)) {
                    signalId = `${type}_${senderId}_${Date.now()}`;
                } else {
                    signalId = `${type}_${senderId}_${typeof payload === 'object' ? JSON.stringify(payload) : payload}`;
                }
            }
            if (!this.processedSignalIds) {
                this.processedSignalIds = new Set();
            }
            if (this.processedSignalIds.has(signalId)) {
                console.log(`[WebRTC CallManager] ⏭ Duplicate signal [${type}] from sender ${senderId} ignored.`);
                return;
            }
            this.processedSignalIds.add(signalId);
            setTimeout(() => {
                if (this.processedSignalIds) this.processedSignalIds.delete(signalId);
            }, 8000);

            console.log(`[WebRTC CallManager] ✅ Handling valid signal [${type}] from sender ${senderId}:`, data);

            switch (type) {
                case 'incoming-call':
                    if (this.callState !== 'idle') {
                        // Check if this signal is from the current active/ringing caller
                        const currentPeerId = this.callerId || this.targetUserId;
                        if (currentPeerId && parseInt(currentPeerId) === parseInt(senderId)) {
                            console.log('[WebRTC CallManager] Duplicate incoming-call from existing peer', senderId, '- ignoring.');
                            return;
                        }
                        // Genuinely on another call with someone else
                        console.log('[WebRTC CallManager] Busy: incoming call from', senderId, 'while currently in state', this.callState, 'with', currentPeerId);
                        this.sendSignal('call-busy', {}, senderId);
                        return;
                    }
                    this.callerId = senderId;
                    this.callerName = data.senderName || 'Colleague';
                    this.remoteUserName = this.callerName;
                    this.callType = payload.callType || 'audio';
                    this.roomId = data.roomId || payload.roomId || null;
                    this.callState = 'ringing';
                    this.startRingTone('incoming');
                    break;

                case 'call-accepted':
                    if (this.callState === 'calling') {
                        if (this.callTimeout) clearTimeout(this.callTimeout);
                        this.stopRingTone();
                        this.callState = 'connected';
                        this.startTimer();
                        await this.setupPeerConnection();
                        
                        // Create WebRTC Offer with audio & video transceivers ready
                        const offer = await this.peerConnection.createOffer({
                            offerToReceiveAudio: true,
                            offerToReceiveVideo: true
                        });
                        await this.peerConnection.setLocalDescription(offer);
                        this.sendSignal('webrtc-offer', { sdp: offer });
                    }
                    break;

                case 'call-declined':
                    if (this.callState === 'calling') {
                        if (this.callTimeout) clearTimeout(this.callTimeout);
                        this.stopRingTone();
                        alert(`${this.remoteUserName} declined the call.`);
                        this.logCallStatus('declined');
                        this.resetCallState();
                    }
                    break;

                case 'call-busy':
                    if (this.callState === 'calling') {
                        if (this.callTimeout) clearTimeout(this.callTimeout);
                        this.stopRingTone();
                        alert(`${this.remoteUserName} is currently on another call.`);
                        this.resetCallState();
                    }
                    break;

                case 'webrtc-offer':
                    if (this.callState === 'connected' && this.peerConnection) {
                        try {
                            const desc = (typeof payload.sdp === 'object' && payload.sdp.type) 
                                ? payload.sdp 
                                : { type: 'offer', sdp: payload.sdp };

                            if (this.peerConnection.signalingState !== 'stable') {
                                await Promise.all([
                                    this.peerConnection.setLocalDescription({ type: 'rollback' }),
                                    this.peerConnection.setRemoteDescription(new RTCSessionDescription(desc))
                                ]);
                            } else {
                                await this.peerConnection.setRemoteDescription(new RTCSessionDescription(desc));
                            }

                            await this.drainIceCandidates();
                            const answer = await this.peerConnection.createAnswer({
                                offerToReceiveAudio: true,
                                offerToReceiveVideo: true
                            });
                            await this.peerConnection.setLocalDescription(answer);
                            this.sendSignal('webrtc-answer', { sdp: answer });
                        } catch (err) {
                            console.error('[WebRTC CallManager] Error handling offer:', err);
                        }
                    }
                    break;

                case 'webrtc-answer':
                    if (this.callState === 'connected' && this.peerConnection) {
                        try {
                            if (this.peerConnection.signalingState === 'have-local-offer') {
                                const desc = (typeof payload.sdp === 'object' && payload.sdp.type) 
                                    ? payload.sdp 
                                    : { type: 'answer', sdp: payload.sdp };
                                await this.peerConnection.setRemoteDescription(new RTCSessionDescription(desc));
                                await this.drainIceCandidates();
                            }
                        } catch (err) {
                            console.error('[WebRTC CallManager] Error handling answer:', err);
                        }
                    }
                    break;

                case 'webrtc-ice-candidate':
                    if (payload && payload.candidate) {
                        const candidate = payload.candidate;
                        if (this.peerConnection && this.peerConnection.remoteDescription && this.peerConnection.remoteDescription.type) {
                            try {
                                await this.peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                            } catch (e) {
                                console.error('[WebRTC CallManager] Error adding ICE candidate:', e);
                            }
                        } else {
                            this.iceCandidateQueue.push(candidate);
                        }
                    }
                    break;

                case 'screen-share-status':
                    console.log('[WebRTC CallManager] Received screen-share-status:', payload);
                    this.remoteHasVideo = (this.callType === 'video') || !!payload.sharing;
                    this.$nextTick(() => {
                        const remoteVideo = document.getElementById('remoteVideo');
                        if (remoteVideo && this.remoteStream) {
                            if (remoteVideo.srcObject !== this.remoteStream) {
                                remoteVideo.srcObject = this.remoteStream;
                            }
                            remoteVideo.play().catch(e => console.warn('[WebRTC] remoteVideo play err on screen status:', e));
                        }
                    });
                    break;

                case 'call-ended':
                    if (this.callTimeout) clearTimeout(this.callTimeout);
                    this.stopRingTone();
                    this.resetCallState();
                    break;
            }
        },

        // ══════════════════════════════════════════════════════════════
        // INCOMING CALL ACTIONS (RECEIVER SIDE)
        // ══════════════════════════════════════════════════════════════
        async acceptCall() {
            if (this.isAccepting) return;
            this.isAccepting = true;

            // Unlock remoteAudio element on user interaction gesture
            const remoteAudio = document.getElementById('remoteAudio');
            if (remoteAudio) {
                remoteAudio.play().catch(() => {});
            }

            this.stopRingTone();
            this.callState = 'connected';
            this.startTimer();

            try {
                // Get media stream with echo cancellation and noise suppression
                this.localStream = await this.getLocalUserMedia();

                this.attachLocalVideo();
                await this.setupPeerConnection();

                // Signal to caller that call is accepted
                this.targetUserId = this.callerId;
                await this.sendSignal('call-accepted', {});

            } catch (err) {
                console.error('[WebRTC CallManager] Error accessing media on accept:', err);
                alert('Microphone / Camera access is required to accept this call.');
                this.declineCall();
            } finally {
                this.isAccepting = false;
            }
        },

        declineCall() {
            this.stopRingTone();
            this.sendSignal('call-declined', {}, this.callerId);
            this.logCallStatus('declined');
            this.resetCallState();
        },

        // ══════════════════════════════════════════════════════════════
        // WEBRTC CONNECTION HANDLING
        // ══════════════════════════════════════════════════════════════
        async drainIceCandidates() {
            if (!this.peerConnection || !this.peerConnection.remoteDescription) return;
            while (this.iceCandidateQueue.length > 0) {
                const cand = this.iceCandidateQueue.shift();
                try {
                    await this.peerConnection.addIceCandidate(new RTCIceCandidate(cand));
                } catch (e) {
                    console.warn('[WebRTC CallManager] Error adding queued ICE candidate:', e);
                }
            }
        },

        async setupPeerConnection() {
            this.peerConnection = new RTCPeerConnection(this.rtcConfig);

            // Add local tracks to peer connection
            if (this.localStream) {
                this.localStream.getTracks().forEach(track => {
                    this.peerConnection.addTrack(track, this.localStream);
                });
            }

            // Handle ICE Candidates
            this.peerConnection.onicecandidate = (event) => {
                if (event.candidate) {
                    const c = event.candidate.toJSON ? event.candidate.toJSON() : event.candidate;
                    this.sendSignal('webrtc-ice-candidate', { candidate: c });
                }
            };

            // Handle Remote Stream Arrival
            this.peerConnection.ontrack = (event) => {
                console.log('[WebRTC CallManager] 🎙 Remote track arrived:', event.track.kind, 'enabled:', event.track.enabled);
                
                if (event.streams && event.streams[0]) {
                    this.remoteStream = event.streams[0];
                } else {
                    if (!this.remoteStream) {
                        this.remoteStream = new MediaStream();
                    }
                    this.remoteStream.addTrack(event.track);
                }

                if (event.track.kind === 'video') {
                    this.remoteHasVideo = true;
                }

                // Dedicated remote audio output (always unmuted)
                const remoteAudio = document.getElementById('remoteAudio');
                if (remoteAudio) {
                    if (remoteAudio.srcObject !== this.remoteStream) {
                        remoteAudio.srcObject = this.remoteStream;
                    }
                    remoteAudio.muted = false;
                    remoteAudio.volume = 1.0;
                    const p = remoteAudio.play();
                    if (p !== undefined) {
                        p.catch(e => console.warn('[WebRTC] remoteAudio autoplay blocked:', e));
                    }
                }

                // Attach to video element (kept muted to avoid audio echo)
                this.$nextTick(() => {
                    const remoteVideo = document.getElementById('remoteVideo');
                    if (remoteVideo) {
                        if (remoteVideo.srcObject !== this.remoteStream) {
                            remoteVideo.srcObject = this.remoteStream;
                        }
                        remoteVideo.muted = true;
                        const vp = remoteVideo.play();
                        if (vp !== undefined) {
                            vp.catch(e => console.warn('[WebRTC] remoteVideo play blocked:', e));
                        }
                    }
                });
            };

            this.peerConnection.oniceconnectionstatechange = () => {
                const iceState = this.peerConnection ? this.peerConnection.iceConnectionState : 'closed';
                console.log('[WebRTC CallManager] ICE connection state:', iceState);
                if (iceState === 'connected' || iceState === 'completed') {
                    this.mediaConnected = true;
                } else if (iceState === 'failed') {
                    console.warn('[WebRTC CallManager] ICE connection failed, restarting ICE if supported...');
                    if (this.peerConnection && this.peerConnection.restartIce) {
                        this.peerConnection.restartIce();
                    }
                }
            };

            this.peerConnection.onconnectionstatechange = () => {
                const state = this.peerConnection ? this.peerConnection.connectionState : 'closed';
                console.log('[WebRTC CallManager] Connection state changed:', state);
                if (state === 'connected') {
                    this.mediaConnected = true;
                } else if (state === 'failed') {
                    console.warn('[WebRTC CallManager] Peer connection failed.');
                }
            };
        },

        async renegotiate() {
            if (!this.peerConnection) return;
            try {
                console.log('[WebRTC CallManager] 🔄 Starting SDP renegotiation...');
                const offer = await this.peerConnection.createOffer({
                    offerToReceiveAudio: true,
                    offerToReceiveVideo: true
                });
                await this.peerConnection.setLocalDescription(offer);
                await this.sendSignal('webrtc-offer', { sdp: offer, renegotiation: true });
            } catch (err) {
                console.error('[WebRTC CallManager] Renegotiation error:', err);
            }
        },

        attachLocalVideo() {
            this.$nextTick(() => {
                const localVideo = document.getElementById('localVideo');
                if (localVideo && this.localStream) {
                    localVideo.srcObject = this.localStream;
                }
                const outgoingLocalVideo = document.getElementById('outgoingLocalVideo');
                if (outgoingLocalVideo && this.localStream) {
                    outgoingLocalVideo.srcObject = this.localStream;
                }
            });
        },

        // ══════════════════════════════════════════════════════════════
        // CONTROLS (MUTE, CAMERA, SCREEN SHARE, HANGUP)
        // ══════════════════════════════════════════════════════════════
        toggleMute() {
            if (!this.localStream) return;
            const audioTracks = this.localStream.getAudioTracks();
            if (audioTracks.length > 0) {
                this.isMuted = !this.isMuted;
                audioTracks[0].enabled = !this.isMuted;
            }
        },

        toggleCamera() {
            if (!this.localStream) return;
            const videoTracks = this.localStream.getVideoTracks();
            if (videoTracks.length > 0) {
                this.isCameraOn = !this.isCameraOn;
                videoTracks[0].enabled = this.isCameraOn;
            }
        },

        async toggleScreenShare() {
            if (!this.isScreenSharing) {
                try {
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getDisplayMedia) {
                        alert('Screen sharing is not supported by your browser or on this device.');
                        return;
                    }

                    this.screenStream = await navigator.mediaDevices.getDisplayMedia({
                        video: {
                            cursor: "always"
                        },
                        audio: false
                    });

                    const screenTrack = this.screenStream.getVideoTracks()[0];
                    if (!screenTrack) {
                        throw new Error('No screen video track found');
                    }

                    // Find existing video sender (video call)
                    const videoSender = this.peerConnection 
                        ? this.peerConnection.getSenders().find(s => s.track && s.track.kind === 'video') 
                        : null;

                    if (videoSender) {
                        console.log('[WebRTC CallManager] Replacing existing video track with screen track');
                        await videoSender.replaceTrack(screenTrack);
                    } else if (this.peerConnection) {
                        console.log('[WebRTC CallManager] Adding new screen track to peer connection');
                        this.screenSender = this.peerConnection.addTrack(screenTrack, this.screenStream);
                        await this.renegotiate();
                    }

                    this.isScreenSharing = true;

                    // Handle browser native stop sharing button
                    screenTrack.onended = () => {
                        console.log('[WebRTC CallManager] Screen share ended via browser controls');
                        this.stopScreenShare();
                    };

                    // Update local PIP preview
                    this.$nextTick(() => {
                        const localVideo = document.getElementById('localVideo');
                        if (localVideo) {
                            localVideo.srcObject = this.screenStream;
                        }
                    });

                    // Notify peer
                    await this.sendSignal('screen-share-status', { sharing: true });

                } catch (err) {
                    console.error('[WebRTC CallManager] Screen share error / cancelled:', err);
                    await this.stopScreenShare();
                }
            } else {
                await this.stopScreenShare();
            }
        },

        async stopScreenShare() {
            const wasSharing = this.isScreenSharing;
            this.isScreenSharing = false;

            if (this.screenStream) {
                this.screenStream.getTracks().forEach(t => {
                    try { t.stop(); } catch(e) {}
                });
                this.screenStream = null;
            }

            if (this.peerConnection) {
                if (this.screenSender) {
                    try {
                        this.peerConnection.removeTrack(this.screenSender);
                    } catch (e) {
                        console.warn('[WebRTC CallManager] Error removing screen track:', e);
                    }
                    this.screenSender = null;
                    await this.renegotiate();
                } else if (this.callType === 'video' && this.localStream) {
                    const videoTrack = this.localStream.getVideoTracks()[0];
                    const videoSender = this.peerConnection.getSenders().find(s => s.track && s.track.kind === 'video');
                    if (videoSender && videoTrack) {
                        await videoSender.replaceTrack(videoTrack);
                    }
                }
            }

            // Re-attach local camera stream if video call
            this.$nextTick(() => {
                const localVideo = document.getElementById('localVideo');
                if (localVideo) {
                    localVideo.srcObject = (this.callType === 'video' && this.localStream) ? this.localStream : null;
                }
            });

            if (wasSharing) {
                await this.sendSignal('screen-share-status', { sharing: false });
            }
        },

        endCall(notifyOtherPeer = true) {
            if (this.callTimeout) clearTimeout(this.callTimeout);
            this.stopRingTone();
            
            if (notifyOtherPeer && (this.targetUserId || this.callerId)) {
                this.sendSignal('call-ended', {});
            }

            if (notifyOtherPeer && this.callState === 'connected') {
                this.logCallStatus('completed');
            }

            this.resetCallState();
        },

        resetCallState() {
            if (this.callTimeout) clearTimeout(this.callTimeout);
            this.stopTimer();
            this.stopRingTone();
            this.isAccepting = false;

            // Stop local media tracks
            if (this.localStream) {
                this.localStream.getTracks().forEach(t => {
                    try { t.stop(); } catch(e) {}
                });
                this.localStream = null;
            }

            if (this.screenStream) {
                this.screenStream.getTracks().forEach(t => {
                    try { t.stop(); } catch(e) {}
                });
                this.screenStream = null;
            }

            // Close peer connection
            if (this.peerConnection) {
                try {
                    this.peerConnection.close();
                } catch(e) {}
                this.peerConnection = null;
            }

            // Clear video & audio elements
            const localVideo = document.getElementById('localVideo');
            if (localVideo) localVideo.srcObject = null;
            const outgoingLocalVideo = document.getElementById('outgoingLocalVideo');
            if (outgoingLocalVideo) outgoingLocalVideo.srcObject = null;
            const remoteVideo = document.getElementById('remoteVideo');
            if (remoteVideo) remoteVideo.srcObject = null;
            const remoteAudio = document.getElementById('remoteAudio');
            if (remoteAudio) {
                remoteAudio.srcObject = null;
                remoteAudio.pause();
            }

            this.callState = 'idle';
            this.targetUserId = null;
            this.callerId = null;
            this.remoteStream = null;
            this.screenSender = null;
            this.remoteHasVideo = false;
            this.mediaConnected = false;
            this.iceCandidateQueue = [];
            this.isMinimized = false;
            this.isMuted = false;
            this.isCameraOn = true;
            this.isScreenSharing = false;
            this.durationSeconds = 0;
        },

        // ══════════════════════════════════════════════════════════════
        // SIGNALING RELAY POST
        // ══════════════════════════════════════════════════════════════
        async sendSignal(type, payload = {}, specificTargetId = null) {
            const targetId = specificTargetId || this.targetUserId || this.callerId;
            console.log(`[WebRTC CallManager] sendSignal [${type}] to target:`, targetId, 'room:', this.roomId);
            if (!targetId) {
                console.warn('[WebRTC CallManager] No targetId found for signal:', type);
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                const res = await axios.post('{{ route('call.signal') }}', {
                    target_user_id: targetId,
                    room_id: this.roomId,
                    type: type,
                    payload: payload
                }, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                console.log(`[WebRTC CallManager] Signal [${type}] sent successfully:`, res.data);
            } catch (err) {
                console.error('[WebRTC CallManager] Signaling send error:', err);
                if (type === 'incoming-call') {
                    alert('Could not send call signal: ' + (err.response?.data?.message || err.message));
                    this.resetCallState();
                }
            }
        },

        async logCallStatus(status) {
            if (!this.roomId) return;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                await axios.post('{{ route('call.log') }}', {
                    room_id: this.roomId,
                    call_type: this.callType,
                    duration_seconds: this.durationSeconds,
                    status: status,
                }, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
            } catch (err) {
                console.error('[WebRTC CallManager] Call logging error:', err);
            }
        },

        // ══════════════════════════════════════════════════════════════
        // CALL TIMER
        // ══════════════════════════════════════════════════════════════
        startTimer() {
            this.stopTimer();
            this.durationSeconds = 0;
            this.timerInterval = setInterval(() => {
                this.durationSeconds++;
            }, 1000);
        },

        stopTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        },

        // ══════════════════════════════════════════════════════════════
        // WEB AUDIO SYNTHESIZED RINGTONES (No external mp3 required)
        // ══════════════════════════════════════════════════════════════
        startRingTone(mode = 'incoming') {
            this.stopRingTone();
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                this.audioContext = new AudioCtx();

                const playTone = () => {
                    if (!this.audioContext) return;
                    const osc1 = this.audioContext.createOscillator();
                    const osc2 = this.audioContext.createOscillator();
                    const gain = this.audioContext.createGain();

                    osc1.connect(gain);
                    osc2.connect(gain);
                    gain.connect(this.audioContext.destination);

                    if (mode === 'incoming') {
                        // UK / European double ring: 440Hz + 480Hz
                        osc1.frequency.setValueAtTime(440, this.audioContext.currentTime);
                        osc2.frequency.setValueAtTime(480, this.audioContext.currentTime);
                        gain.gain.setValueAtTime(0.12, this.audioContext.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, this.audioContext.currentTime + 1.2);
                        osc1.start();
                        osc2.start();
                        osc1.stop(this.audioContext.currentTime + 1.2);
                        osc2.stop(this.audioContext.currentTime + 1.2);
                    } else {
                        // Outgoing soft ringing tone: 440Hz + 480Hz
                        osc1.frequency.setValueAtTime(440, this.audioContext.currentTime);
                        osc2.frequency.setValueAtTime(480, this.audioContext.currentTime);
                        gain.gain.setValueAtTime(0.07, this.audioContext.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, this.audioContext.currentTime + 1.5);
                        osc1.start();
                        osc2.start();
                        osc1.stop(this.audioContext.currentTime + 1.5);
                        osc2.stop(this.audioContext.currentTime + 1.5);
                    }
                };

                playTone();
                this.ringtoneInterval = setInterval(playTone, mode === 'incoming' ? 2500 : 3500);

            } catch (e) {
                console.warn('[WebAudio] Synthesizer not allowed before user gesture:', e);
            }
        },

        stopRingTone() {
            if (this.ringtoneInterval) {
                clearInterval(this.ringtoneInterval);
                this.ringtoneInterval = null;
            }
            if (this.audioContext) {
                try {
                    this.audioContext.close();
                } catch (e) {}
                this.audioContext = null;
            }
        }
    };
}

// Register globally and with Alpine
window.callManagerComponent = callManagerComponent;
if (window.Alpine) {
    window.Alpine.data('callManagerComponent', callManagerComponent);
}
document.addEventListener('alpine:init', () => {
    Alpine.data('callManagerComponent', callManagerComponent);
});
</script>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- DEDICATED STYLES FOR THE CALL SYSTEM (BULLET-PROOF CSS)        -->
<!-- ══════════════════════════════════════════════════════════════ -->
<style>
[x-cloak] {
    display: none !important;
}

.crm-call-root {
    position: relative;
    z-index: 2147483640;
}

/* 1. Incoming Call Dialog */
.crm-incoming-dialog {
    position: fixed !important;
    top: 24px !important;
    right: 24px !important;
    z-index: 2147483647 !important;
    width: 390px !important;
    max-width: calc(100vw - 32px) !important;
    background: #0f172a !important;
    border: 1px solid rgba(100, 116, 139, 0.4) !important;
    border-radius: 24px !important;
    padding: 22px !important;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.05) !important;
    color: #ffffff !important;
    font-family: inherit !important;
    box-sizing: border-box !important;
    animation: crmSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.crm-incoming-header {
    display: flex !important;
    align-items: center !important;
    gap: 16px !important;
    margin-bottom: 20px !important;
}

.crm-avatar-ring {
    position: relative !important;
    width: 58px !important;
    height: 58px !important;
    flex-shrink: 0 !important;
}

.crm-avatar {
    width: 58px !important;
    height: 58px !important;
    border-radius: 18px !important;
    background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 22px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.5) !important;
}

.crm-pulse-circle {
    position: absolute !important;
    inset: -4px !important;
    border-radius: 22px !important;
    border: 2px solid rgba(99, 102, 241, 0.6) !important;
    animation: crmPing 1.6s cubic-bezier(0, 0, 0.2, 1) infinite !important;
    pointer-events: none !important;
}

.crm-incoming-info {
    min-width: 0 !important;
    flex: 1 !important;
}

.crm-badge {
    display: inline-block !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    padding: 3px 10px !important;
    border-radius: 9999px !important;
    margin-bottom: 6px !important;
}

.crm-badge-audio {
    background: rgba(16, 185, 129, 0.15) !important;
    color: #34d399 !important;
    border: 1px solid rgba(16, 185, 129, 0.3) !important;
}

.crm-badge-video {
    background: rgba(99, 102, 241, 0.15) !important;
    color: #a5b4fc !important;
    border: 1px solid rgba(99, 102, 241, 0.3) !important;
}

.crm-caller-name {
    font-size: 17px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    margin: 0 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.crm-caller-status {
    font-size: 12px !important;
    color: #94a3b8 !important;
    margin: 3px 0 0 0 !important;
}

.crm-incoming-actions {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
}

/* Buttons */
.crm-btn {
    border: none !important;
    border-radius: 14px !important;
    padding: 12px 18px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 8px !important;
    transition: transform 0.15s ease, background-color 0.15s ease !important;
    box-sizing: border-box !important;
}
.crm-btn:active {
    transform: scale(0.97) !important;
}

.crm-btn-decline {
    flex: 1 !important;
    background: rgba(239, 68, 68, 0.15) !important;
    color: #fca5a5 !important;
    border: 1px solid rgba(239, 68, 68, 0.4) !important;
}
.crm-btn-decline:hover {
    background: rgba(239, 68, 68, 0.25) !important;
}

.crm-btn-accept {
    flex: 1 !important;
    background: #10b981 !important;
    color: #ffffff !important;
    box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.5) !important;
    animation: crmPulseGlow 1.5s infinite !important;
}
.crm-btn-accept:hover {
    background: #059669 !important;
}

.crm-btn-hangup {
    background: #ef4444 !important;
    color: #ffffff !important;
    box-shadow: 0 10px 20px -5px rgba(239, 68, 68, 0.5) !important;
}
.crm-btn-hangup:hover {
    background: #dc2626 !important;
}

/* 2. Outgoing Calling Modal */
.crm-calling-window {
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    z-index: 2147483647 !important;
    width: 420px !important;
    max-width: 92vw !important;
    font-family: inherit !important;
    box-sizing: border-box !important;
    animation: crmFadeScale 0.25s ease-out;
}

.crm-calling-card {
    background: #0f172a !important;
    border: 1px solid rgba(100, 116, 139, 0.4) !important;
    border-radius: 28px !important;
    padding: 32px 24px !important;
    text-align: center !important;
    box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.85) !important;
    color: #ffffff !important;
}

.crm-avatar-calling {
    width: 100px !important;
    height: 100px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 38px !important;
    font-weight: 800 !important;
    margin: 0 auto !important;
    position: relative !important;
    box-shadow: 0 15px 30px rgba(99, 102, 241, 0.4) !important;
}

.crm-local-preview-box {
    width: 120px !important;
    height: 120px !important;
    border-radius: 24px !important;
    overflow: hidden !important;
    border: 2px solid #6366f1 !important;
    background: #000 !important;
    margin: 0 auto !important;
}

.crm-calling-target {
    font-size: 20px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    margin: 10px 0 4px 0 !important;
}

.crm-calling-subtext {
    font-size: 13px !important;
    color: #818cf8 !important;
    margin: 0 0 24px 0 !important;
    animation: crmPulseText 1.5s infinite !important;
}

.crm-calling-controls {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 12px !important;
}

/* 3. Connected Window */
.crm-connected-container {
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    z-index: 2147483647 !important;
    width: 680px !important;
    max-width: 95vw !important;
    height: 520px !important;
    max-height: 85vh !important;
    font-family: inherit !important;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
    box-sizing: border-box !important;
}

.crm-connected-container.crm-minimized {
    top: auto !important;
    left: auto !important;
    bottom: 24px !important;
    right: 24px !important;
    transform: none !important;
    width: 320px !important;
    height: auto !important;
}

.crm-connected-window {
    background: #0f172a !important;
    border: 1px solid rgba(100, 116, 139, 0.4) !important;
    border-radius: 24px !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
    height: 100% !important;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.9) !important;
    color: #ffffff !important;
}

.crm-header-bar {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 12px 20px !important;
    background: #1e293b !important;
    border-bottom: 1px solid rgba(51, 65, 85, 0.6) !important;
    flex-shrink: 0 !important;
}

.crm-header-left {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
}

.crm-live-dot {
    width: 8px !important;
    height: 8px !important;
    border-radius: 50% !important;
    background: #10b981 !important;
    box-shadow: 0 0 8px #10b981 !important;
}

.crm-header-name {
    font-size: 13px !important;
    font-weight: 700 !important;
    color: #f1f5f9 !important;
}

.crm-header-timer {
    font-size: 11px !important;
    font-family: monospace !important;
    color: #94a3b8 !important;
    background: rgba(0, 0, 0, 0.3) !important;
    padding: 2px 6px !important;
    border-radius: 6px !important;
}

.crm-icon-btn {
    background: transparent !important;
    border: none !important;
    color: #94a3b8 !important;
    padding: 6px !important;
    border-radius: 8px !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.crm-icon-btn:hover {
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.1) !important;
}

.crm-media-body {
    flex: 1 !important;
    position: relative !important;
    background: #020617 !important;
    overflow: hidden !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.crm-video-viewport {
    width: 100% !important;
    height: 100% !important;
    position: relative !important;
    background: #000000 !important;
}

.crm-video-feed {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
}

.crm-pip-box {
    position: absolute !important;
    bottom: 16px !important;
    right: 16px !important;
    width: 140px !important;
    height: 100px !important;
    border-radius: 14px !important;
    overflow: hidden !important;
    border: 2px solid #475569 !important;
    background: #0f172a !important;
    box-shadow: 0 10px 25px rgba(0,0,0,0.6) !important;
}

.crm-pip-label {
    position: absolute !important;
    bottom: 4px !important;
    left: 6px !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    background: rgba(0, 0, 0, 0.7) !important;
    padding: 1px 6px !important;
    border-radius: 4px !important;
}

.crm-audio-viewport {
    width: 100% !important;
    height: 100% !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 24px !important;
}

.crm-avatar-audio-box {
    position: relative !important;
    width: 90px !important;
    height: 90px !important;
}

.crm-avatar-audio {
    width: 90px !important;
    height: 90px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #6366f1, #8b5cf6) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 34px !important;
    font-weight: 800 !important;
    color: #ffffff !important;
}

.crm-duration-pill {
    font-size: 13px !important;
    font-family: monospace !important;
    font-weight: 700 !important;
    color: #34d399 !important;
    background: rgba(16, 185, 129, 0.15) !important;
    border: 1px solid rgba(16, 185, 129, 0.3) !important;
    padding: 4px 14px !important;
    border-radius: 9999px !important;
    margin-top: 12px !important;
}

.crm-footer-bar {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 12px !important;
    padding: 14px 20px !important;
    background: #0f172a !important;
    border-top: 1px solid rgba(51, 65, 85, 0.6) !important;
    flex-shrink: 0 !important;
}

.crm-ctrl-btn {
    width: 44px !important;
    height: 44px !important;
    border-radius: 12px !important;
    background: #1e293b !important;
    border: 1px solid #334155 !important;
    color: #f1f5f9 !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.15s ease !important;
}
.crm-ctrl-btn:hover {
    background: #334155 !important;
}
.crm-ctrl-btn.crm-ctrl-btn-active {
    background: #ef4444 !important;
    border-color: #ef4444 !important;
    color: #ffffff !important;
}

@keyframes crmSlideIn {
    from { opacity: 0; transform: translateY(-16px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes crmFadeScale {
    from { opacity: 0; transform: translate(-50%, -50%) scale(0.92); }
    to { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}
@keyframes crmPing {
    0% { transform: scale(1); opacity: 0.8; }
    80%, 100% { transform: scale(1.4); opacity: 0; }
}
@keyframes crmPulseGlow {
    0%, 100% { box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.5); }
    50% { box-shadow: 0 10px 25px 2px rgba(16, 185, 129, 0.8); }
}
@keyframes crmPulseText {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- HTML MARKUP FOR CALL OVERLAYS                                 -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div x-data="callManagerComponent()" 
     x-init="init()"
     @start-call.window="startCall($event.detail)"
     class="crm-call-root">

    <!-- Dedicated audio element for remote stream playback -->
    <audio id="remoteAudio" autoplay playsinline style="position: fixed; top: -9999px; left: -9999px; width: 1px; height: 1px; opacity: 0.01; pointer-events: none;"></audio>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- 1. INCOMING CALL MODAL (RECEIVER SIDE)                      -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div x-cloak
         style="display: none;"
         x-show="callState === 'ringing'" 
         class="crm-incoming-dialog">
        
        <div class="crm-incoming-header">
            <div class="crm-avatar-ring">
                <div class="crm-avatar">
                    <span x-text="callerName ? callerName.charAt(0).toUpperCase() : 'U'"></span>
                </div>
                <div class="crm-pulse-circle"></div>
            </div>

            <div class="crm-incoming-info">
                <div class="crm-badge" :class="callType === 'video' ? 'crm-badge-video' : 'crm-badge-audio'">
                    <span x-text="callType === 'video' ? '📹 Incoming Video Call' : '📞 Incoming Voice Call'"></span>
                </div>
                <h3 class="crm-caller-name" x-text="callerName || 'Colleague'"></h3>
                <p class="crm-caller-status">Calling you...</p>
            </div>
        </div>

        <div class="crm-incoming-actions">
            <!-- Decline Button -->
            <button type="button" @click="declineCall()" class="crm-btn crm-btn-decline">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span>Decline</span>
            </button>

            <!-- Accept Button -->
            <button type="button" @click="acceptCall()" class="crm-btn crm-btn-accept">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <span>Accept</span>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- 2. OUTGOING CALL WINDOW (CALLER SIDE)                       -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div x-cloak
         style="display: none;"
         x-show="callState === 'calling'" 
         class="crm-calling-window">
        
        <div class="crm-calling-card">
            <!-- Top info badge -->
            <div class="crm-badge" :class="callType === 'video' ? 'crm-badge-video' : 'crm-badge-audio'" style="margin: 0 auto 16px auto;">
                <span x-text="callType === 'video' ? '📹 Outgoing Video Call' : '📞 Outgoing Voice Call'"></span>
            </div>

            <!-- Avatar or local video preview -->
            <div style="position: relative; width: 120px; height: 120px; margin: 0 auto 18px auto;">
                <template x-if="callType === 'video'">
                    <div class="crm-local-preview-box">
                        <video id="outgoingLocalVideo" autoplay playsinline muted class="crm-video-feed" style="transform: scaleX(-1);"></video>
                    </div>
                </template>
                <template x-if="callType === 'audio'">
                    <div class="crm-avatar-calling">
                        <span x-text="remoteUserName ? remoteUserName.charAt(0).toUpperCase() : 'U'"></span>
                        <div class="crm-pulse-circle"></div>
                    </div>
                </template>
            </div>

            <h3 class="crm-calling-target" x-text="remoteUserName || 'Calling...'"></h3>
            <p class="crm-calling-subtext">Ringing...</p>

            <!-- Caller controls -->
            <div class="crm-calling-controls">
                <!-- Mute Toggle -->
                <button type="button" @click="toggleMute()" 
                        class="crm-ctrl-btn" 
                        :class="isMuted ? 'crm-ctrl-btn-active' : ''"
                        :title="isMuted ? 'Unmute' : 'Mute'">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!isMuted">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                    <svg x-cloak width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="isMuted">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                    </svg>
                </button>

                <!-- Cancel / End Call Button -->
                <button type="button" @click="endCall(true)" class="crm-btn crm-btn-hangup" style="flex: 1;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"/>
                    </svg>
                    <span>Cancel Call</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════ -->
    <!-- 3. ACTIVE CONNECTED CALL WINDOW (BOTH PEERS)                -->
    <!-- ═══════════════════════════════════════════════════════════ -->
    <div x-cloak
         style="display: none;"
         x-show="callState === 'connected'" 
         class="crm-connected-container"
         :class="isMinimized ? 'crm-minimized' : ''">

        <div class="crm-connected-window">
            
            <!-- Window Header -->
            <div class="crm-header-bar">
                <div class="crm-header-left">
                    <span class="crm-live-dot" :style="mediaConnected ? 'background: #10b981; box-shadow: 0 0 8px #10b981;' : 'background: #f59e0b; box-shadow: 0 0 8px #f59e0b;'"></span>
                    <span class="crm-header-name" x-text="remoteUserName"></span>
                    <span class="crm-header-timer" x-text="mediaConnected ? formattedDuration : 'Connecting...'"></span>
                </div>
                <div class="crm-header-right">
                    <!-- Minimize / Expand -->
                    <button type="button" @click="isMinimized = !isMinimized" class="crm-icon-btn" :title="isMinimized ? 'Maximize' : 'Minimize'">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!isMinimized">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        <svg x-cloak width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="isMinimized">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Main Viewport (Visible when not minimized) -->
            <div class="crm-media-body" x-show="!isMinimized">
                
                <!-- Video Call OR Screen Sharing Viewport -->
                <template x-if="callType === 'video' || isScreenSharing || remoteHasVideo">
                    <div class="crm-video-viewport">
                        <!-- Remote Video / Screen Feed -->
                        <video id="remoteVideo" autoplay playsinline class="crm-video-feed" style="width: 100%; height: 100%; object-fit: contain; background: #000;"></video>

                        <!-- Local Video / Screen PIP -->
                        <div class="crm-pip-box" x-show="callType === 'video' || isScreenSharing">
                            <video id="localVideo" autoplay playsinline muted class="crm-video-feed" :style="isScreenSharing ? 'object-fit: contain; transform: none;' : 'object-fit: cover; transform: scaleX(-1);'"></video>
                            <span class="crm-pip-label" x-text="isScreenSharing ? 'Your Screen' : 'You'"></span>
                        </div>
                    </div>
                </template>

                <!-- Audio-Only Call Viewport (when neither side is sharing video or screen) -->
                <template x-if="!(callType === 'video' || isScreenSharing || remoteHasVideo)">
                    <div class="crm-audio-viewport">
                        <div class="crm-avatar-audio-box">
                            <div class="crm-avatar-audio">
                                <span x-text="remoteUserName ? remoteUserName.charAt(0).toUpperCase() : 'U'"></span>
                            </div>
                            <div class="crm-pulse-circle"></div>
                        </div>
                        <h3 class="crm-calling-target" style="margin-top: 16px;" x-text="remoteUserName"></h3>
                        <p class="crm-calling-subtext" :style="mediaConnected ? 'color: #10b981;' : 'color: #f59e0b;'" x-text="mediaConnected ? 'Connected' : 'Connecting...'"></p>
                        <div class="crm-duration-pill" x-text="formattedDuration"></div>
                    </div>
                </template>
            </div>

            <!-- Controls Footer Bar -->
            <div class="crm-footer-bar">
                
                <!-- Toggle Mute Button -->
                <button type="button" @click="toggleMute()" 
                        class="crm-ctrl-btn" 
                        :class="isMuted ? 'crm-ctrl-btn-active' : ''"
                        :title="isMuted ? 'Unmute Mic' : 'Mute Mic'">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!isMuted">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                    <svg x-cloak width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="isMuted">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                    </svg>
                </button>

                <!-- Toggle Camera Button (Video Call only) -->
                <button type="button" x-cloak x-show="callType === 'video'" @click="toggleCamera()" 
                        class="crm-ctrl-btn" 
                        :class="!isCameraOn ? 'crm-ctrl-btn-active' : ''"
                        :title="isCameraOn ? 'Turn Off Camera' : 'Turn On Camera'">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="isCameraOn">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <svg x-cloak width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!isCameraOn">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </button>

                <!-- Screen Share (Available in both Audio and Video Calls) -->
                <button type="button" @click="toggleScreenShare()" 
                        class="crm-ctrl-btn" 
                        :class="isScreenSharing ? 'crm-ctrl-btn-active' : ''"
                        :title="isScreenSharing ? 'Stop Screen Share' : 'Share Screen'">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </button>

                <!-- End Call Button (Big Red) -->
                <button type="button" @click="endCall(true)" class="crm-btn crm-btn-hangup" style="padding: 10px 22px;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"/>
                    </svg>
                    <span>End Call</span>
                </button>
            </div>

        </div>
    </div>

</div>
