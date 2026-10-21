/**
 * NetLearn Pro - Sound Effects Engine
 * يستخدم ملفات الصوت المهنية المحملة في assets/audio/ مع استدعاء مرن ودعم كتم الصوت
 */

window.SoundFX = (() => {
    // تحديد المسار النسبي الصحيح للمجلد assets/audio/
    const getAudioPath = (filename) => {
        const inStudentOrAdmin = window.location.pathname.includes('/student/') || window.location.pathname.includes('/admin/');
        return (inStudentOrAdmin ? '../' : '') + 'assets/audio/' + filename;
    };

    const soundMap = {
        click: 'button_click.ogg',
        packet: 'packet_send.ogg',
        hop: 'hop_pulse.ogg',
        firewall: 'firewall_pass.ogg',
        success: 'success_fanfare.ogg',
        alert: 'link_alert.ogg',
        correct: 'quiz_correct.ogg',
        levelUp: 'level_up.ogg'
    };

    // التخزين المؤقت للكائنات الصوتية
    const audioCache = {};
    let isMuted = localStorage.getItem('netlearn_sound_muted') === 'true';

    // تحميل مسبق للصوت
    const getAudio = (key) => {
        if (!soundMap[key]) return null;
        if (!audioCache[key]) {
            const audio = new Audio(getAudioPath(soundMap[key]));
            audio.preload = 'auto';
            audioCache[key] = audio;
        }
        return audioCache[key];
    };

    const play = (key, volume = 0.5) => {
        if (isMuted) return;
        try {
            const audio = getAudio(key);
            if (!audio) return;
            // إعادة ضبط الوقت لتشغيل الصوت بسرعة عند التكرار
            audio.currentTime = 0;
            audio.volume = Math.max(0, Math.min(1, volume));
            const promise = audio.play();
            if (promise !== undefined) {
                promise.catch(() => {
                    // Autoplay restriction or user hasn't interacted yet
                });
            }
        } catch (e) {
            console.debug('Sound play skipped:', e);
        }
    };

    const toggleMute = () => {
        isMuted = !isMuted;
        localStorage.setItem('netlearn_sound_muted', isMuted);
        return isMuted;
    };

    const getMuted = () => isMuted;

    return {
        play,
        toggleMute,
        getMuted,
        soundMap
    };
})();
