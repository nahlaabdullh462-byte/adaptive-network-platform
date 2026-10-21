<?php
$pageTitle = "المحلل التفاعلي لطبقات الشبكة (OSI & TCP/IP 7-Layer Analyzer)";
require_once '../config/security.php';
require_auth('student');
require_once '../includes/header.php';
?>

<div style="margin-bottom: 25px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
        <div>
            <span class="badge badge-primary" style="margin-bottom: 8px;">
                <i class="fa-solid fa-layer-group"></i> معمارية نماذج الشبكات المرجعية (OSI & TCP/IP Reference Models)
            </span>
            <h1 style="font-size: 1.75rem; color: var(--text-heading); margin-bottom: 6px;">
                المحلل التفاعلي للطبقات السبع ومحاكي تغليف البيانات (Encapsulation Analyzer)
            </h1>
            <p style="font-size: 0.94rem; color: var(--text-muted); margin: 0; max-width: 820px;">
                استكشف وظائف طبقات نموذج OSI السبع ومقارنتها بنموذج TCP/IP، وشاهد بالرسوم المتحركة التفاعلية كيف تتحول البيانات من Application Data إلى Bits عبر مسار التغليف وفك التغليف.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="dashboard.php" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-house"></i> لوحة التحكم
            </a>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- محاكي التغليف وفك التغليف الحي (Live Encapsulation Animator)               -->
<!-- ========================================================================= -->
<div class="academic-card" style="margin-bottom: 25px; border-top: 3px solid var(--brand-primary); padding: 26px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h3 style="font-size: 1.2rem; color: var(--text-heading); margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-arrows-split-up-and-left" style="color: var(--brand-cyan);"></i>
                محاكي تغليف وفك تغليف حزم البيانات (Data Encapsulation & Decapsulation)
            </h3>
            <span style="font-size: 0.84rem; color: var(--text-muted);">شاهد كيف تُضاف ترويسات البروتوكولات عند الإرسال وتُزال عند الاستقبال.</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="button" class="btn btn-primary btn-sm" onclick="startEncapsulationSimulation()">
                <i class="fa-solid fa-play"></i> بدء محاكاة التغليف (Send Data)
            </button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="resetEncapsulationSimulation()">
                <i class="fa-solid fa-arrows-rotate"></i> إعادة تعيين
            </button>
        </div>
    </div>

    <!-- مسرح الرسوم المتحركة للتغليف -->
    <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-main); border-radius: var(--radius-md); padding: 24px; min-height: 180px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
        
        <div id="sim-status-badge" class="badge badge-cyan" style="margin-bottom: 16px; font-size: 0.85rem;">
            جاهز للمحاكاة: اضغط على "بدء محاكاة التغليف"
        </div>

        <!-- الصندوق البصري للحزمة المتغيرة -->
        <div id="packet-container" style="display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap; transition: all 0.4s ease;">
            <div id="pdu-data" style="background: #2563EB; color: #FFFFFF; padding: 12px 24px; border-radius: var(--radius-sm); font-weight: bold; font-family: var(--font-mono); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);">
                [HTTP Payload: GET /index.html]
            </div>
            <div id="pdu-tcp" style="display: none; background: #6366F1; color: #FFFFFF; padding: 12px 18px; border-radius: var(--radius-sm); font-weight: bold; font-family: var(--font-mono); box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);">
                + [TCP Port 443]
            </div>
            <div id="pdu-ip" style="display: none; background: #0D9488; color: #FFFFFF; padding: 12px 18px; border-radius: var(--radius-sm); font-weight: bold; font-family: var(--font-mono); box-shadow: 0 4px 12px rgba(13, 148, 136, 0.4);">
                + [IP 192.168.1.5 -> 8.8.8.8]
            </div>
            <div id="pdu-eth" style="display: none; background: #D97706; color: #FFFFFF; padding: 12px 18px; border-radius: var(--radius-sm); font-weight: bold; font-family: var(--font-mono); box-shadow: 0 4px 12px rgba(217, 119, 6, 0.4);">
                + [MAC Frame & FCS]
            </div>
            <div id="pdu-bits" style="display: none; background: #10B981; color: #0F172A; padding: 12px 20px; border-radius: var(--radius-sm); font-weight: 800; font-family: var(--font-mono); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);">
                01001000 01100101 01101100 01101100 01101111...
            </div>
        </div>

        <div id="sim-explanation" style="margin-top: 18px; font-size: 0.92rem; color: var(--text-body); max-width: 650px; line-height: 1.6;">
            تمثل هذه الوحدة البيانات الصادرة من تطبيق الويب (Application Layer Payload) قبل إضافة ترويسات النقل والشبكة.
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- مصفوفة الطبقات السبع التفاعلية (7 OSI Layers Stack Matrix)                -->
<!-- ========================================================================= -->
<div class="grid-2" style="gap: 25px;">
    
    <!-- القائمة العمودية للطبقات السبع -->
    <div class="academic-card" style="padding: 24px;">
        <h3 style="font-size: 1.15rem; color: var(--text-heading); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-bars-staggered" style="color: var(--brand-primary);"></i>
            برج طبقات نموذج OSI (اضغط على أي طبقة للتفاصيل)
        </h3>

        <div style="display: flex; flex-direction: column; gap: 8px;">
            <div class="layer-item active" onclick="selectLayer(7)" id="layer-btn-7" style="background: rgba(37, 99, 235, 0.2); border: 1.5px solid var(--brand-primary); padding: 14px 18px; border-radius: var(--radius-sm); cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="badge badge-primary">Layer 7</span>
                    <strong style="color: #FFFFFF; font-size: 1rem;">طبقة التطبيقات (Application Layer)</strong>
                </div>
                <span class="code-font" style="color: var(--brand-cyan); font-size: 0.85rem;">PDU: Data</span>
            </div>

            <div class="layer-item" onclick="selectLayer(6)" id="layer-btn-6" style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-main); padding: 14px 18px; border-radius: var(--radius-sm); cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="badge badge-secondary">Layer 6</span>
                    <strong style="color: var(--text-heading); font-size: 1rem;">طبقة التقديم والعرض (Presentation Layer)</strong>
                </div>
                <span class="code-font" style="color: var(--brand-cyan); font-size: 0.85rem;">PDU: Data</span>
            </div>

            <div class="layer-item" onclick="selectLayer(5)" id="layer-btn-5" style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-main); padding: 14px 18px; border-radius: var(--radius-sm); cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="badge badge-secondary">Layer 5</span>
                    <strong style="color: var(--text-heading); font-size: 1rem;">طبقة الجلسة (Session Layer)</strong>
                </div>
                <span class="code-font" style="color: var(--brand-cyan); font-size: 0.85rem;">PDU: Data</span>
            </div>

            <div class="layer-item" onclick="selectLayer(4)" id="layer-btn-4" style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-main); padding: 14px 18px; border-radius: var(--radius-sm); cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="badge badge-cyan">Layer 4</span>
                    <strong style="color: var(--text-heading); font-size: 1rem;">طبقة النقل (Transport Layer)</strong>
                </div>
                <span class="code-font" style="color: var(--brand-cyan); font-size: 0.85rem;">PDU: Segment</span>
            </div>

            <div class="layer-item" onclick="selectLayer(3)" id="layer-btn-3" style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-main); padding: 14px 18px; border-radius: var(--radius-sm); cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="badge badge-cyan">Layer 3</span>
                    <strong style="color: var(--text-heading); font-size: 1rem;">طبقة الشبكة (Network Layer)</strong>
                </div>
                <span class="code-font" style="color: var(--brand-cyan); font-size: 0.85rem;">PDU: Packet</span>
            </div>

            <div class="layer-item" onclick="selectLayer(2)" id="layer-btn-2" style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-main); padding: 14px 18px; border-radius: var(--radius-sm); cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="badge badge-warning">Layer 2</span>
                    <strong style="color: var(--text-heading); font-size: 1rem;">طبقة ربط البيانات (Data Link Layer)</strong>
                </div>
                <span class="code-font" style="color: var(--brand-cyan); font-size: 0.85rem;">PDU: Frame</span>
            </div>

            <div class="layer-item" onclick="selectLayer(1)" id="layer-btn-1" style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-main); padding: 14px 18px; border-radius: var(--radius-sm); cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span class="badge badge-danger">Layer 1</span>
                    <strong style="color: var(--text-heading); font-size: 1rem;">الطبقة الفيزيائية (Physical Layer)</strong>
                </div>
                <span class="code-font" style="color: var(--brand-cyan); font-size: 0.85rem;">PDU: Bits</span>
            </div>
        </div>
    </div>

    <!-- بطاقة تفاصيل الطبقة المختارة -->
    <div class="academic-card" style="padding: 24px; border-top: 3px solid var(--brand-cyan);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <span id="detail-layer-num" class="badge badge-primary">المستوى رقم #7</span>
            <span id="detail-layer-tcp" class="badge badge-secondary">يقابلها في TCP/IP: Application</span>
        </div>

        <h3 id="detail-layer-title" style="font-size: 1.4rem; color: var(--text-heading); margin-bottom: 12px;">
            طبقة التطبيقات (Application Layer)
        </h3>

        <p id="detail-layer-desc" style="font-size: 0.94rem; color: var(--text-body); line-height: 1.65; margin-bottom: 20px;">
            الواجهة المباشرة التي يتفاعل معها المستخدم وبرامجه. توفر خدمات الشبكة للبرمجيات مثل تصفح الويب، ونقل الملفات، والبريد الإلكتروني، والترجمة الاسمية.
        </p>

        <div class="calc-result-grid" style="margin-bottom: 20px;">
            <div class="calc-metric-box">
                <div class="calc-metric-label">وحدة بيانات البروتوكول (PDU):</div>
                <div id="detail-layer-pdu" class="calc-metric-value" style="color: var(--brand-cyan);">Data</div>
            </div>

            <div class="calc-metric-box">
                <div class="calc-metric-label">الأجهزة الهندسية المرتبطة:</div>
                <div id="detail-layer-devices" class="calc-metric-value" style="font-size: 1rem; color: #FFFFFF;">Gateway, Firewall L7, Hosts</div>
            </div>
        </div>

        <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-main); padding: 16px; border-radius: var(--radius-sm); margin-bottom: 16px;">
            <strong style="color: var(--brand-cyan); font-size: 0.88rem; display: block; margin-bottom: 6px;">
                <i class="fa-solid fa-network-wired"></i> أبرز البروتوكولات العاملة في هذه الطبقة:
            </strong>
            <div id="detail-layer-protocols" style="font-family: var(--font-mono); font-size: 0.92rem; color: #FFFFFF;">
                HTTP, HTTPS, DNS, DHCP, FTP, SSH, Telnet, SMTP
            </div>
        </div>

        <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid var(--brand-primary-border); padding: 16px; border-radius: var(--radius-sm);">
            <strong style="color: var(--brand-primary); font-size: 0.88rem; display: block; margin-bottom: 4px;">
                <i class="fa-solid fa-graduation-cap"></i> متطلبات اختبار Cisco CCNA:
            </strong>
            <div id="detail-layer-ccna" style="font-size: 0.88rem; color: var(--text-body); line-height: 1.6;">
                التركيز على الفروق بين HTTP (منفذ 80) و HTTPS (منفذ 443)، وكيفية عمل استعلامات DNS عبر بروتوكول UDP/53.
            </div>
        </div>

    </div>

</div>

<!-- ========================================================================= -->
<!-- محرك جافاسكريبت التفاعلي لطبقات OSI                                        -->
<!-- ========================================================================= -->
<script>
const layerData = {
    7: {
        num: 'المستوى رقم #7',
        tcp: 'يقابلها في TCP/IP: Application Layer',
        title: 'طبقة التطبيقات (Application Layer)',
        desc: 'الواجهة المباشرة التي يتفاعل معها المستخدم وبرامجه. توفر خدمات الشبكة للبرمجيات مثل تصفح الويب، ونقل الملفات، والبريد الإلكتروني، والترجمة الاسمية.',
        pdu: 'Data',
        devices: 'Application Gateway, Proxy, L7 Firewall',
        protocols: 'HTTP, HTTPS, DNS, DHCP, FTP, SSH, SMTP',
        ccna: 'التركيز على الفروق بين HTTP (80) و HTTPS (443)، وكيفية عمل استعلامات DNS (53) وعملية DORA في DHCP.'
    },
    6: {
        num: 'المستوى رقم #6',
        tcp: 'يقابلها في TCP/IP: Application Layer',
        title: 'طبقة التقديم والعرض (Presentation Layer)',
        desc: 'مسؤولة عن تنسيق البيانات، وضغطها، وتشفيرها وفك تشفيرها (Encryption/Decryption) لتكون بصيغة قياسية مفهومة للطرفين.',
        pdu: 'Data',
        devices: 'End Host Operating System, TLS Processors',
        protocols: 'TLS/SSL, ASCII, Unicode, JPEG, GIF, MP4',
        ccna: 'فهم بروتوكول TLS 1.3 في تأمين الاتصالات وتشفير البيانات المتدفقة عبر بروتوكولات الويب.'
    },
    5: {
        num: 'المستوى رقم #5',
        tcp: 'يقابلها في TCP/IP: Application Layer',
        title: 'طبقة الجلسة (Session Layer)',
        desc: 'مسؤولة عن فتح وإدارة ومزامنة وإغلاق جلسات الاتصال الحوارية بين التطبيقات على الأجهزة المختلفة (Session Management).',
        pdu: 'Data',
        devices: 'Operating System Sockets, API Endpoints',
        protocols: 'NetBIOS, RPC, PPTP, SOCKS',
        ccna: 'إدراك دور نقاط النهاية ومقابس الشبكة (Sockets) المكونة من IP + Port Number.'
    },
    4: {
        num: 'المستوى رقم #4',
        tcp: 'يقابلها في TCP/IP: Transport Layer',
        title: 'طبقة النقل (Transport Layer)',
        desc: 'مسؤولة عن نقل البيانات طرفاً لطرف (End-to-End)، والتحكم في التدفق (Flow Control)، والتسلسل (Sequencing)، وضمان التوصيل الموثوق عبر أرقام المنافذ.',
        pdu: 'Segment (TCP) / Datagram (UDP)',
        devices: 'Firewall L4, L4 Load Balancer',
        protocols: 'TCP (Connection-oriented), UDP (Connectionless)',
        ccna: 'معرفة آلية المصافحة الثلاثية 3-Way Handshake (SYN -> SYN-ACK -> ACK) والفروق الجوهرية بين TCP و UDP.'
    },
    3: {
        num: 'المستوى رقم #3',
        tcp: 'يقابلها في TCP/IP: Internet Layer',
        title: 'طبقة الشبكة (Network Layer)',
        desc: 'مسؤولة عن التوجيه المنطقي (Logical Addressing) واختيار أفضل مسار لحزم البيانات (Path Determination) بين الشبكات المختلفة.',
        pdu: 'Packet (حزمة بيانات)',
        devices: 'Router, Layer 3 Switch, Multilayer Switch',
        protocols: 'IPv4, IPv6, ICMP, ARP, OSPF, EIGRP, BGP',
        ccna: 'أساسيات توجيه الراوتر، عناوين الـ IP، تجزئة الشبكات Subnetting، وفحص مسارات بروتوكول OSPF.'
    },
    2: {
        num: 'المستوى رقم #2',
        tcp: 'يقابلها في TCP/IP: Network Access Layer',
        title: 'طبقة ربط البيانات (Data Link Layer)',
        desc: 'مسؤولة عن العنونة الفيزيائية (MAC Address) ونقل الإطارات داخل الشبكة المحلية الواحدة واكتشاف أخطاء الإرسال عبر حقل FCS.',
        pdu: 'Frame (إطار بيانات)',
        devices: 'Layer 2 Switch, Bridge, Wireless Access Point',
        protocols: 'Ethernet (802.3), Wi-Fi (802.11), PPP, HDLC, CDP',
        ccna: 'هيكل إطار الـ Ethernet، جداول الـ MAC Address Table، بروتوكول STP لمنع الحلقات، وشبكات الـ VLANs.'
    },
    1: {
        num: 'المستوى رقم #1',
        tcp: 'يقابلها في TCP/IP: Network Access Layer',
        title: 'الطبقة الفيزيائية (Physical Layer)',
        desc: 'مسؤولة عن إرسال واستقبال الإشارات الكهربائية أو الضوئية أو الراديوية الخام عبر وسائط النقل السلكية واللاسلكية.',
        pdu: 'Bits (بتات 0 و 1)',
        devices: 'Hub, Repeater, UTP Cables, Fiber Optics',
        protocols: '1000BASE-T, RJ45, V.35, RS-232, DSL',
        ccna: 'أنواع كابلات UTP (Straight-through vs Crossover)، معايير السرعة، وموصلات الألياف الضوئية (Single-mode vs Multi-mode).'
    }
};

function selectLayer(id) {
    document.querySelectorAll('.layer-item').forEach(el => {
        el.style.background = 'rgba(15, 23, 42, 0.7)';
        el.style.border = '1px solid var(--border-main)';
    });
    const active = document.getElementById(`layer-btn-${id}`);
    if (active) {
        active.style.background = 'rgba(37, 99, 235, 0.2)';
        active.style.border = '1.5px solid var(--brand-primary)';
    }

    const d = layerData[id];
    document.getElementById('detail-layer-num').textContent = d.num;
    document.getElementById('detail-layer-tcp').textContent = d.tcp;
    document.getElementById('detail-layer-title').textContent = d.title;
    document.getElementById('detail-layer-desc').textContent = d.desc;
    document.getElementById('detail-layer-pdu').textContent = d.pdu;
    document.getElementById('detail-layer-devices').textContent = d.devices;
    document.getElementById('detail-layer-protocols').textContent = d.protocols;
    document.getElementById('detail-layer-ccna').textContent = d.ccna;
}

// محاكاة التغليف التفاعلي بالأنيميشن
let encStep = 0;
let encInterval = null;

function startEncapsulationSimulation() {
    resetEncapsulationSimulation();
    const steps = [
        { badge: 'الخطوة 1: طبقة التطبيقات (L7-L5)', tcp: false, ip: false, eth: false, bits: false, expl: 'يولد المتصفح طلب HTTP GET ويتم تجهيز البيانات كـ Application Payload.' },
        { badge: 'الخطوة 2: طبقة النقل (L4 Transport)', tcp: true, ip: false, eth: false, bits: false, expl: 'تتم إضافة ترويسة TCP لتحديد أرقام المنافذ (Port 443) والتحكم بالتسلسل؛ تتحول البيانات إلى Segment.' },
        { badge: 'الخطوة 3: طبقة الشبكة (L3 Network)', tcp: true, ip: true, eth: false, bits: false, expl: 'تتم إضافة ترويسة IP لتحديد عنوان المصدر والوجهة (Source & Dest IP)؛ تتحول إلى Packet.' },
        { badge: 'الخطوة 4: طبقة ربط البيانات (L2 Data Link)', tcp: true, ip: true, eth: true, bits: false, expl: 'تتم إضافة ترويسة Ethernet وعناوين MAC وحقل التحقق من الأخطاء FCS؛ تتحول إلى Frame.' },
        { badge: 'الخطوة 5: الطبقة الفيزيائية (L1 Physical)', tcp: true, ip: true, eth: true, bits: true, expl: 'يتم تحويل الإطار إلى إشارات كهربائية أو نبضات ضوئية على هيئة بتات (Bits) وإرسالها عبر السلك.' }
    ];

    encInterval = setInterval(() => {
        if (encStep < steps.length) {
            const s = steps[encStep];
            document.getElementById('sim-status-badge').textContent = s.badge;
            document.getElementById('sim-explanation').textContent = s.expl;
            if (s.tcp) document.getElementById('pdu-tcp').style.display = 'block';
            if (s.ip) document.getElementById('pdu-ip').style.display = 'block';
            if (s.eth) document.getElementById('pdu-eth').style.display = 'block';
            if (s.bits) document.getElementById('pdu-bits').style.display = 'block';
            encStep++;
        } else {
            clearInterval(encInterval);
            document.getElementById('sim-status-badge').className = 'badge badge-success';
            document.getElementById('sim-status-badge').innerHTML = '<i class="fa-solid fa-circle-check"></i> اكتمل التغليف بنجاح ووصلت الحزمة للطرف المقابل!';
        }
    }, 1100);
}

function resetEncapsulationSimulation() {
    if (encInterval) clearInterval(encInterval);
    encStep = 0;
    document.getElementById('pdu-tcp').style.display = 'none';
    document.getElementById('pdu-ip').style.display = 'none';
    document.getElementById('pdu-eth').style.display = 'none';
    document.getElementById('pdu-bits').style.display = 'none';
    document.getElementById('sim-status-badge').className = 'badge badge-cyan';
    document.getElementById('sim-status-badge').textContent = 'جاهز للمحاكاة: اضغط على "بدء محاكاة التغليف"';
    document.getElementById('sim-explanation').textContent = 'تمثل هذه الوحدة البيانات الصادرة من تطبيق الويب (Application Layer Payload) قبل إضافة ترويسات النقل والشبكة.';
}
</script>

<?php require_once '../includes/footer.php'; ?>
