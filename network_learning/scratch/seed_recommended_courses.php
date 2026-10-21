<?php
require_once __DIR__ . '/../config/db.php';

$courses = [
    // ── LEVEL 1 ──
    [
        'level_id' => 1,
        'topic_keyword' => 'نموذج OSI والطبقات السبع',
        'title' => 'دورة هندسة الشبكات Cisco CCNA 200-301 بالعربي (قائمة تشغيل كاملة)',
        'instructor' => 'م. عادل الحميدي / Network Academy',
        'youtube_id' => '1z0Z8FFnc8c',
        'youtube_url' => 'https://www.youtube.com/watch?v=1z0Z8FFnc8c',
        'playlist_url' => 'https://www.youtube.com/playlist?list=PL1UX3jI2z7XW4x8y7SXZ15k6hD8h5t_Qj',
        'duration' => '45+ فيديو تدريبي',
        'content_type' => 'full_playlist',
        'badge_text' => 'الدورة الموصى بها رسمياً ⭐',
        'description' => 'الشرح العربي الأكثر شمولاً لمعايير CCNA، يبدأ من الصفر وصولاً إلى أعلى مستويات الاحتراف مع تطبيقات Packet Tracer.'
    ],
    [
        'level_id' => 1,
        'topic_keyword' => 'نموذج OSI والطبقات السبع',
        'title' => 'الدليل العملي لفهم طبقات OSI السبع وتدفق البيانات (OSI Model Explained)',
        'instructor' => 'FreeCodeCamp & Tech Arabic',
        'youtube_id' => 'vv4y_uOneC0',
        'youtube_url' => 'https://www.youtube.com/watch?v=vv4y_uOneC0',
        'playlist_url' => null,
        'duration' => '24 دقيقة',
        'content_type' => 'targeted_video',
        'badge_text' => 'مرشح لتقوية الضعف ⚡',
        'description' => 'تفكيك بصري ثلاثي الأبعاد لكيفية انتقال البيانات بين الطبقات ووحدات PDU والمنافذ.'
    ],
    [
        'level_id' => 1,
        'topic_keyword' => 'بروتوكولات النقل TCP و UDP',
        'title' => 'كيف يعمل اتصال TCP؟ شرح المصافحة الثلاثية عملياً عبر Wireshark',
        'instructor' => 'NetworkChuck Arabic / Tech Simplified',
        'youtube_id' => 'i5Uv4dK5m7Y',
        'youtube_url' => 'https://www.youtube.com/watch?v=i5Uv4dK5m7Y',
        'playlist_url' => null,
        'duration' => '18 دقيقة',
        'content_type' => 'targeted_video',
        'badge_text' => 'تطبيقي مباشر 🛠️',
        'description' => 'التقاط حزم SYN و SYN-ACK و ACK عملياً وفحص ترويسة الحزمة خطوة بخطوة.'
    ],
    [
        'level_id' => 1,
        'topic_keyword' => 'أجهزة الشبكة ومجالات التصادم',
        'title' => 'الفرق الجوهري بين Hub و Switch و Router ومجالات البث والتصادم',
        'instructor' => 'Cisco Certified Arabic',
        'youtube_id' => 'NMPX5U8v4A8',
        'youtube_url' => 'https://www.youtube.com/watch?v=NMPX5U8v4A8',
        'playlist_url' => null,
        'duration' => '16 دقيقة',
        'content_type' => 'targeted_video',
        'badge_text' => 'مفاهيم معمارية أساسية 💡',
        'description' => 'فهم كيف يفصل المبدل مجالات التصادم، ولماذا الراوتر هو الوحيد الذي يفصل مجالات البث العام.'
    ],

    // ── LEVEL 2 ──
    [
        'level_id' => 2,
        'topic_keyword' => 'تجزئة الشبكات Subnetting',
        'title' => 'ماستر كلاس تقطيع شبكات IPv4 وحسابات Subnetting ذهنياً بدون ورقة وقلم',
        'instructor' => 'المهندس أحمد نظمي / Ahmed Nazmy',
        'youtube_id' => 's_NathP39pg',
        'youtube_url' => 'https://www.youtube.com/watch?v=s_NathP39pg',
        'playlist_url' => 'https://www.youtube.com/playlist?list=PLBlnK6fEyqRgMCUAG0XRw78UA8qnv6jEx',
        'duration' => 'سلسلة من 8 دروس',
        'content_type' => 'full_playlist',
        'badge_text' => 'أعلى تقييم للتقطيع ⭐',
        'description' => 'تقنيات سريعة لحساب أقنعة /25 حتى /30 واستخراج Network ID و Broadcast في ثوانٍ.'
    ],
    [
        'level_id' => 2,
        'topic_keyword' => 'تجزئة الشبكات Subnetting',
        'title' => 'كيفية تطبيق تقطيع الشبكات متغيرة الطول (VLSM Design Step-by-Step)',
        'instructor' => 'Practical Networking Arabic',
        'youtube_id' => 'rs39Wnaydcg',
        'youtube_url' => 'https://www.youtube.com/watch?v=rs39Wnaydcg',
        'playlist_url' => null,
        'duration' => '22 دقيقة',
        'content_type' => 'targeted_video',
        'badge_text' => 'مرشح لتقوية الضعف ⚡',
        'description' => 'ترتيب متطلبات المضيفين تنازلياً وتوزيع العناوين على الروابط النقطية ومباني الفروع بدقة.'
    ],
    [
        'level_id' => 2,
        'topic_keyword' => 'الشبكات المحلية الافتراضية VLANs',
        'title' => 'إعداد شبكات VLAN وروابط الـ Trunk وبروتوكول 802.1Q عملياً على سيسكو',
        'instructor' => 'Cisco Packet Tracer Labs',
        'youtube_id' => '4_Z0N_b0Gvg',
        'youtube_url' => 'https://www.youtube.com/watch?v=4_Z0N_b0Gvg',
        'playlist_url' => null,
        'duration' => '28 دقيقة',
        'content_type' => 'hands_on_lab',
        'badge_text' => 'مختبر عملي سيسكو 🛠️',
        'description' => 'أوامر switchport mode trunk و switchport access vlan وتوجيه Inter-VLAN عبر Router-on-a-Stick.'
    ],
    [
        'level_id' => 2,
        'topic_keyword' => 'معمارية عنونة IPv4',
        'title' => 'الفرق بين Public IP و Private IP وعناوين APIPA والـ Loopback',
        'instructor' => 'Network Fundamentals Arabic',
        'youtube_id' => '7_8X5Y2_v1w',
        'youtube_url' => 'https://www.youtube.com/watch?v=7_8X5Y2_v1w',
        'playlist_url' => null,
        'duration' => '15 دقيقة',
        'content_type' => 'targeted_video',
        'badge_text' => 'أساسيات هامة 💡',
        'description' => 'فئات العناوين Class A, B, C, D ومجالات العناوين المسموح باستخدامها داخلياً.'
    ],

    // ── LEVEL 3 ──
    [
        'level_id' => 3,
        'topic_keyword' => 'بروتوكول OSPF وخوارزمية SPF',
        'title' => 'دورة بروتوكول OSPF ومناطق Area 0 وتعيين DR/BDR وتكلفة المسار',
        'instructor' => 'Jeremy\'s IT Lab / CCNA 200-301',
        'youtube_id' => 'tP3_r6Zl8x0',
        'youtube_url' => 'https://www.youtube.com/watch?v=tP3_r6Zl8x0',
        'playlist_url' => 'https://www.youtube.com/playlist?list=PLxbwE86jKRgMpuZuLBivmtM8dww0CX58n',
        'duration' => '10 فيديوهات متسلسلة',
        'content_type' => 'full_playlist',
        'badge_text' => 'أفضل شرح لـ OSPF ⭐',
        'description' => 'بناء طوبولوجيا OSPF متعددة المناطق، وحساب Cost Metric، وتوزيع المسار الافتراضي default-information originate.'
    ],
    [
        'level_id' => 3,
        'topic_keyword' => 'بروتوكول STP والوقاية من الحلقات',
        'title' => 'محاكاة Spanning Tree Protocol (STP) وانتخاب Root Bridge وحجب المنافذ',
        'instructor' => 'Network Direction Arabic',
        'youtube_id' => 'japVwP__aA0',
        'youtube_url' => 'https://www.youtube.com/watch?v=japVwP__aA0',
        'playlist_url' => null,
        'duration' => '26 دقيقة',
        'content_type' => 'targeted_video',
        'badge_text' => 'مرشح لتقوية الضعف ⚡',
        'description' => 'معيار 802.1D و RSTP 802.1w، وقيمة Bridge ID، وحالات Blocking و Listening و Learning و Forwarding.'
    ],
    [
        'level_id' => 3,
        'topic_keyword' => 'أساسيات التوجيه الثابت والديناميكي',
        'title' => 'المسافة الإدارية (Administrative Distance) والمسار الثابت العائم Floating Static',
        'instructor' => 'Cisco Routing Academy',
        'youtube_id' => '9X2_k7W_4e0',
        'youtube_url' => 'https://www.youtube.com/watch?v=9X2_k7W_4e0',
        'playlist_url' => null,
        'duration' => '19 دقيقة',
        'content_type' => 'targeted_video',
        'badge_text' => 'سيناريو هندسي دقيق 💡',
        'description' => 'لماذا يفضل الراوتر المسار الثابت على OSPF؟ وكيفية بناء مسار احتياطي عبر تعديل الـ AD.'
    ],
    [
        'level_id' => 3,
        'topic_keyword' => 'بروتوكول STP والوقاية من الحلقات',
        'title' => 'تطبيق ميزة EtherChannel عبر بروتوكول LACP المعياري على مبدلات سيسكو',
        'instructor' => 'Packet Tracer Tutorials',
        'youtube_id' => 'mG_hWzH_7F4',
        'youtube_url' => 'https://www.youtube.com/watch?v=mG_hWzH_7F4',
        'playlist_url' => null,
        'duration' => '21 دقيقة',
        'content_type' => 'hands_on_lab',
        'badge_text' => 'مختبر عملي سيسكو 🛠️',
        'description' => 'أوامر channel-group 1 mode active لدمج 4 منافذ Gigabit في منفذ Port-Channel واحد.'
    ],

    // ── LEVEL 4 ──
    [
        'level_id' => 4,
        'topic_keyword' => 'قوائم التحكم بالوصول ACLs',
        'title' => 'كورس أمن البنية التحتية وقوائم ACL والجدران النارية وأنفاق IPsec VPN',
        'instructor' => 'م. أحمد سلطان / Cyber Security & Networks',
        'youtube_id' => '9Tq8Z6U_5J8',
        'youtube_url' => 'https://www.youtube.com/watch?v=9Tq8Z6U_5J8',
        'playlist_url' => 'https://www.youtube.com/playlist?list=PLG_f_V_x6Zq9p_u-9_hX_4_d',
        'duration' => '14 فيديو تدريبي',
        'content_type' => 'full_playlist',
        'badge_text' => 'دورة أمن الشبكات الموصى بها ⭐',
        'description' => 'كتابة قوائم Standard و Extended ACL، وفحص حركة البيانات، وحماية بيئات العمل ضد الاختراقات.'
    ],
    [
        'level_id' => 4,
        'topic_keyword' => 'الجدران النارية وفحص الحالة',
        'title' => 'شرح ترجمة العناوين والمنافذ NAT Overload (PAT) وتكوينها عملياً',
        'instructor' => 'Tech Acad Arabic',
        'youtube_id' => 'V1Y6X_j0wM4',
        'youtube_url' => 'https://www.youtube.com/watch?v=V1Y6X_j0wM4',
        'playlist_url' => null,
        'duration' => '25 دقيقة',
        'content_type' => 'targeted_video',
        'badge_text' => 'تطبيقي مباشر 🛠️',
        'description' => 'أوامر ip nat inside, ip nat outside, و ip nat inside source list 1 interface g0/0 overload.'
    ],
    [
        'level_id' => 4,
        'topic_keyword' => 'هندسة الأمان Zero Trust',
        'title' => 'شرح Zero Trust Architecture وميزات DHCP Snooping و Dynamic ARP Inspection',
        'instructor' => 'Enterprise Security Arabic',
        'youtube_id' => '8Pq_x5H_b9w',
        'youtube_url' => 'https://www.youtube.com/watch?v=8Pq_x5H_b9w',
        'playlist_url' => null,
        'duration' => '30 دقيقة',
        'content_type' => 'targeted_video',
        'badge_text' => 'مرشح لتقوية الضعف ⚡',
        'description' => 'كيف تمنع هجمات Rogue DHCP و ARP Spoofing، ولماذا مبدأ Never Trust, Always Verify هو مستقبل أمن الشبكات.'
    ],
    [
        'level_id' => 4,
        'topic_keyword' => 'الشبكات الافتراضية الخاصة VPN',
        'title' => 'كيف يعمل نفق IPsec VPN؟ الفارق بين Phase 1 و Phase 2 وبروتوكول ESP',
        'instructor' => 'Cisco Security Academy',
        'youtube_id' => '3L9_w4Q_8z1',
        'youtube_url' => 'https://www.youtube.com/watch?v=3L9_w4Q_8z1',
        'playlist_url' => null,
        'duration' => '22 دقيقة',
        'content_type' => 'targeted_video',
        'badge_text' => 'تشفير وأمان متقدم 🔒',
        'description' => 'مفاوضات ISAKMP، وتبادل المفاتيح عبر Diffie-Hellman، وتشفير البيانات عبر ESP.'
    ]
];

// مسح أي بيانات سابقة وإعادة الإدخال
$pdo->exec("TRUNCATE TABLE `recommended_courses`");

$ins = $pdo->prepare("INSERT INTO `recommended_courses` 
    (`level_id`, `topic_keyword`, `title`, `instructor`, `youtube_id`, `youtube_url`, `playlist_url`, `duration`, `content_type`, `badge_text`, `description`) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$cnt = 0;
foreach ($courses as $c) {
    $ins->execute([
        $c['level_id'],
        $c['topic_keyword'],
        $c['title'],
        $c['instructor'],
        $c['youtube_id'],
        $c['youtube_url'],
        $c['playlist_url'],
        $c['duration'],
        $c['content_type'],
        $c['badge_text'],
        $c['description']
    ]);
    $cnt++;
}

echo "Successfully seeded {$cnt} curated YouTube CCNA courses!\n";
