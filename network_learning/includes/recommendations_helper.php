<?php
/**
 * Adaptive Video & YouTube Recommendation Engine
 * NetLearn Pro (AI-Powered Educational Video Hub)
 */

if (!function_exists('get_adaptive_course_recommendations')) {
    function get_adaptive_course_recommendations($pdo, $level_id, $weak_topic = null) {
        $params = [(int)$level_id];
        $sql = "SELECT * FROM recommended_courses WHERE level_id = ?";

        if (!empty($weak_topic)) {
            $sql .= " ORDER BY (CASE WHEN topic_keyword LIKE ? THEN 0 ELSE 1 END), content_type ASC, id ASC";
            $params[] = '%' . trim($weak_topic) . '%';
        } else {
            $sql .= " ORDER BY content_type ASC, id ASC";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!function_exists('render_adaptive_video_hub')) {
    function render_adaptive_video_hub($pdo, $level_id, $weak_topic = null, $custom_title = null, $custom_subtitle = null) {
        $courses = get_adaptive_course_recommendations($pdo, $level_id, $weak_topic);
        if (empty($courses)) return;

        $title = $custom_title ?? "المسار المرئي المقترح بالذكاء الاصطناعي (YouTube Adaptive Hub)";
        $subtitle = $custom_subtitle ?? (!empty($weak_topic) 
            ? "بناءً على تشخيص نتائجك، اخترنا لك هذه الفيديوهات وقوائم التشغيل المركزة لتقوية موضوع «" . htmlspecialchars($weak_topic) . "»:"
            : "شروحات فيديو تفاعلية وقوائم تشغيل معتمدة لشرح منهج هذا المستوى خطوة بخطوة:");
        ?>

        <style>
        /* ── نمط بطاقات الفيديو التفاعلية (Video Hub Cards) ── */
        .video-hub-section {
            background: #FFFFFF;
            border: 2px solid rgba(91, 79, 232, 0.22);
            border-radius: var(--radius-xl);
            padding: 34px 30px;
            margin: 32px 0 24px;
            box-shadow: 0 16px 45px -10px rgba(91, 79, 232, 0.12);
            position: relative;
            overflow: hidden;
        }
        .video-hub-section::before {
            content: '';
            position: absolute;
            top: 0; right: 0; width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(91, 79, 232, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .video-hub-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 1.5px solid var(--border-subtle);
        }

        .video-hub-title {
            font-size: 1.35rem;
            font-weight: 900;
            color: var(--text-heading);
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 0 6px;
        }

        .video-filter-bar {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .video-filter-btn {
            padding: 7px 16px;
            border-radius: var(--radius-full);
            font-size: 0.82rem;
            font-weight: 700;
            border: 1px solid var(--border-main);
            background: var(--bg-surface-solid);
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.25s ease;
        }
        .video-filter-btn:hover {
            color: var(--brand-primary);
            border-color: var(--brand-primary-border);
        }
        .video-filter-btn.active {
            background: var(--brand-gradient);
            color: #FFFFFF !important;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(91, 79, 232, 0.25);
        }

        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .video-card {
            background: var(--bg-surface-card);
            border: 1.5px solid rgba(208, 213, 229, 0.65);
            border-radius: var(--radius-lg);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            box-shadow: var(--shadow-subtle);
        }
        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 40px -12px rgba(26, 26, 62, 0.16);
            border-color: rgba(91, 79, 232, 0.4);
        }

        .video-thumb-wrap {
            position: relative;
            width: 100%;
            height: 165px;
            background: #0F172A;
            overflow: hidden;
            cursor: pointer;
        }
        .video-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .video-card:hover .video-thumb-img {
            transform: scale(1.08);
            filter: brightness(0.85);
        }

        .video-play-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.35);
            transition: background 0.3s ease;
        }
        .video-card:hover .video-play-overlay {
            background: rgba(15, 23, 42, 0.55);
        }
        .video-play-btn {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.95);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.5);
            transition: transform 0.3s ease, background 0.3s ease;
        }
        .video-card:hover .video-play-btn {
            transform: scale(1.15);
            background: #EF4444;
        }

        .video-duration-pill {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(15, 23, 42, 0.88);
            color: #FFFFFF;
            font-family: var(--font-mono);
            font-size: 0.74rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: var(--radius-sm);
            backdrop-filter: blur(4px);
        }

        .video-card-body {
            padding: 18px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .video-badge-tag {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 3px 10px;
            border-radius: var(--radius-full);
            margin-bottom: 8px;
            align-self: flex-start;
        }
        .video-badge-tag.highlight {
            background: #FEF3C7;
            color: #92400E;
            border: 1px solid #FDE68A;
        }
        .video-badge-tag.playlist {
            background: #EEEDFF;
            color: var(--brand-primary);
            border: 1px solid var(--brand-primary-border);
        }
        .video-badge-tag.lab {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .video-card-title {
            font-size: 0.98rem;
            font-weight: 800;
            color: var(--text-heading);
            line-height: 1.45;
            margin: 0 0 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-card-instructor {
            font-size: 0.82rem;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .video-card-desc {
            font-size: 0.84rem;
            color: var(--text-body);
            line-height: 1.55;
            margin-bottom: 16px;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-card-actions {
            display: flex;
            gap: 8px;
            margin-top: auto;
        }

        /* ── مشغل الفيديو المنبثق التفاعلي (Interactive Modal Player) ── */
        .video-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.3s ease forwards;
        }
        .video-modal-window {
            background: #0F172A;
            border: 1.5px solid rgba(139, 92, 246, 0.35);
            border-radius: var(--radius-xl);
            width: 100%;
            max-width: 900px;
            overflow: hidden;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            animation: scaleUp 0.3s ease forwards;
        }
        .video-modal-header {
            padding: 16px 22px;
            background: #1E293B;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .video-modal-title {
            color: #FFFFFF;
            font-size: 1.05rem;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .video-modal-close {
            background: none;
            border: none;
            color: #94A3B8;
            font-size: 1.4rem;
            cursor: pointer;
            padding: 4px;
            transition: color 0.2s ease;
        }
        .video-modal-close:hover {
            color: #EF4444;
        }
        .video-modal-iframe-box {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            height: 0;
            background: #000000;
        }
        .video-modal-iframe-box iframe {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            border: none;
        }
        .video-modal-footer {
            padding: 14px 22px;
            background: #1E293B;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        @keyframes scaleUp {
            from { transform: scale(0.92); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        </style>

        <section class="video-hub-section" id="adaptive-video-hub">
            <div class="video-hub-header">
                <div>
                    <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(91,79,232,0.08); padding:5px 14px; border-radius:var(--radius-full); font-size:0.8rem; font-weight:800; color:var(--brand-primary); margin-bottom:10px;">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> خوارزمية التوصية المرئية التكيفية (YouTube Edu AI)
                    </div>
                    <h2 class="video-hub-title">
                        <i class="fa-brands fa-youtube" style="color:#EF4444; font-size:1.6rem;"></i>
                        <?php echo htmlspecialchars($title); ?>
                    </h2>
                    <p style="color:var(--text-muted); font-size:0.94rem; margin:0; line-height:1.6;">
                        <?php echo $subtitle; ?>
                    </p>
                </div>

                <span class="badge badge-primary" style="font-size:0.85rem; padding:8px 16px;">
                    <i class="fa-solid fa-layer-group"></i> <?php echo count($courses); ?> موارد معتمدة
                </span>
            </div>

            <!-- أزرار التصفية الفورية -->
            <div class="video-filter-bar">
                <button type="button" class="video-filter-btn active" onclick="filterVideoCards('all')">
                    <i class="fa-solid fa-border-all"></i> جميع المقترحات (<?php echo count($courses); ?>)
                </button>
                <button type="button" class="video-filter-btn" onclick="filterVideoCards('targeted_video')">
                    <i class="fa-solid fa-bolt" style="color:#F59E0B;"></i> فيديوهات التقوية المركزة
                </button>
                <button type="button" class="video-filter-btn" onclick="filterVideoCards('full_playlist')">
                    <i class="fa-solid fa-list-check" style="color:var(--brand-primary);"></i> سلاسل وقوائم كاملة Playlist
                </button>
                <button type="button" class="video-filter-btn" onclick="filterVideoCards('hands_on_lab')">
                    <i class="fa-solid fa-flask" style="color:#10B981;"></i> مختبرات عملية Packet Tracer
                </button>
            </div>

            <!-- شبكة بطاقات الكورسات -->
            <div class="video-grid" id="video-cards-grid">
                <?php foreach ($courses as $c): ?>
                    <?php 
                        $is_weak_match = (!empty($weak_topic) && stripos($c['topic_keyword'], $weak_topic) !== false);
                        $thumb_url = "https://img.youtube.com/vi/" . $c['youtube_id'] . "/hqdefault.jpg";
                    ?>
                    <div class="video-card" data-type="<?php echo htmlspecialchars($c['content_type']); ?>">
                        
                        <!-- Thumbnail مع زر التشغيل المباشر -->
                        <div class="video-thumb-wrap" onclick="openVideoModal('<?php echo htmlspecialchars($c['youtube_id']); ?>', '<?php echo addslashes($c['title']); ?>', '<?php echo htmlspecialchars($c['youtube_url']); ?>')">
                            <img src="<?php echo $thumb_url; ?>" alt="<?php echo htmlspecialchars($c['title']); ?>" class="video-thumb-img" onerror="this.src='https://images.unsplash.com/photo-1544197150-b99a580bb7a8?w=500&auto=format&fit=crop';">
                            <div class="video-play-overlay">
                                <div class="video-play-btn">
                                    <i class="fa-solid fa-play"></i>
                                </div>
                            </div>
                            <span class="video-duration-pill">
                                <i class="fa-solid fa-clock"></i> <?php echo htmlspecialchars($c['duration']); ?>
                            </span>
                        </div>

                        <!-- المحتوى -->
                        <div class="video-card-body">
                            <span class="video-badge-tag <?php echo $is_weak_match ? 'highlight' : ($c['content_type'] === 'full_playlist' ? 'playlist' : 'lab'); ?>">
                                <?php echo htmlspecialchars($c['badge_text'] ?? 'شرح معتمد'); ?>
                            </span>

                            <h3 class="video-card-title" title="<?php echo htmlspecialchars($c['title']); ?>">
                                <?php echo clean_output($c['title']); ?>
                            </h3>

                            <div class="video-card-instructor">
                                <i class="fa-solid fa-chalkboard-user" style="color:var(--brand-primary);"></i>
                                <?php echo clean_output($c['instructor']); ?>
                            </div>

                            <p class="video-card-desc">
                                <?php echo clean_output($c['description']); ?>
                            </p>

                            <div class="video-card-actions">
                                <button type="button" onclick="openVideoModal('<?php echo htmlspecialchars($c['youtube_id']); ?>', '<?php echo addslashes($c['title']); ?>', '<?php echo htmlspecialchars($c['youtube_url']); ?>')" class="btn btn-primary btn-sm" style="flex:2; font-weight:700;">
                                    <i class="fa-solid fa-play"></i> مشاهدة تفاعلية
                                </button>
                                
                                <?php if (!empty($c['playlist_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($c['playlist_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm" title="فتح قائمة التشغيل كاملة على يوتيوب" style="flex:1;">
                                        <i class="fa-solid fa-list"></i> السلسلة
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo htmlspecialchars($c['youtube_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm" title="فتح على يوتيوب" style="flex:1;">
                                        <i class="fa-brands fa-youtube" style="color:#EF4444;"></i> يوتيوب
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

        </section>

        <!-- نافذة مشغل الفيديو المنبثقة التفاعلية (Video Modal Window) -->
        <div class="video-modal-backdrop" id="video-modal-backdrop" onclick="closeVideoModalOnBackdrop(event)">
            <div class="video-modal-window">
                <div class="video-modal-header">
                    <h3 class="video-modal-title">
                        <i class="fa-brands fa-youtube" style="color:#EF4444;"></i>
                        <span id="modal-video-title">عنوان الدرس</span>
                    </h3>
                    <button type="button" class="video-modal-close" onclick="closeVideoModal()" title="إغلاق">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="video-modal-iframe-box">
                    <iframe id="modal-video-iframe" src="" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>

                <div class="video-modal-footer">
                    <span style="color:#94A3B8; font-size:0.86rem;">
                        <i class="fa-solid fa-circle-info"></i> المشاهدة المباشرة دون إعلانات داخل بيئة NetLearn Pro.
                    </span>
                    <a href="#" id="modal-video-extlink" target="_blank" rel="noopener noreferrer" class="btn btn-sm" style="background:rgba(255,255,255,0.1); color:#FFF !important; border:1px solid rgba(255,255,255,0.2);">
                        <i class="fa-brands fa-youtube" style="color:#EF4444;"></i> فتح الرابط على يوتيوب ↗
                    </a>
                </div>
            </div>
        </div>

        <script>
        // تصفية البطاقات
        function filterVideoCards(type) {
            document.querySelectorAll('.video-filter-btn').forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');

            const cards = document.querySelectorAll('#video-cards-grid .video-card');
            cards.forEach(card => {
                if (type === 'all' || card.dataset.type === type) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // تشغيل وإغلاق نافذة المشغل المنبثقة
        function openVideoModal(youtubeId, title, externalUrl) {
            const backdrop = document.getElementById('video-modal-backdrop');
            const iframe = document.getElementById('modal-video-iframe');
            const titleEl = document.getElementById('modal-video-title');
            const extLink = document.getElementById('modal-video-extlink');

            titleEl.textContent = title;
            extLink.href = externalUrl;
            iframe.src = 'https://www.youtube.com/embed/' + youtubeId + '?autoplay=1&rel=0';

            backdrop.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeVideoModal() {
            const backdrop = document.getElementById('video-modal-backdrop');
            const iframe = document.getElementById('modal-video-iframe');

            iframe.src = '';
            backdrop.style.display = 'none';
            document.body.style.overflow = '';
        }

        function closeVideoModalOnBackdrop(e) {
            if (e.target.id === 'video-modal-backdrop') {
                closeVideoModal();
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeVideoModal();
            }
        });
        </script>
        <?php
    }
}
