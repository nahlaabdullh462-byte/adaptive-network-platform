<?php
/**
 * Seed Rich, Realistic, Advanced Study Modules for NetLearn Pro
 * Designed for Cisco CCNA 200-301 Real-World Learning
 */
require_once __DIR__ . '/../config/db.php';

$module1_html = <<<'HTML'
<div class="study-guide-content">

    <!-- شريط بطاقة أهداف التعلم المعتمدة -->
    <div class="callout-box" id="sec-overview">
        <h4><i class="fa-solid fa-graduation-cap"></i> أهداف التعلم الهندسية المعتمدة (Cisco CCNA 200-301 Objectives):</h4>
        <p>
            1. <strong>المعمارية الهرمية:</strong> فهم التسلسل المعياري للطبقات السبع من الطبقة الفيزيائية وصولاً لطبقة التطبيقات.<br>
            2. <strong>وحدات نقل البيانات (PDU):</strong> إتقان الفروق بين Bits و Frames و Packets و Segments و Data.<br>
            3. <strong>التغليف وتدفق البيانات:</strong> تتبع إضافة ترويسات التحكم (Headers) ومقطورات الأخطاء (Trailers) أثناء الإرسال وفكها عند الاستقبال.<br>
            4. <strong>عزل المجالات:</strong> التمييز الحاسم بين مجالات التصادم (Collision Domains) ومجالات البث (Broadcast Domains).<br>
            5. <strong>أوامر سيسكو للتحقق:</strong> التعرف على أوامر Cisco IOS الأساسية لفحص حالة المنافذ وعناوين MAC.
        </p>
    </div>

    <!-- القسم الأول: الطبقات السبع -->
    <h3 id="sec-osi-stack"><i class="fa-solid fa-layer-group"></i> 1. المعمارية الهرمية لنموذج الاتصال المعياري (OSI 7 Layers Reference Model)</h3>
    <p>
        تم تطوير نموذج <strong>OSI (Open Systems Interconnection)</strong> بواسطة المنظمة الدولية للمعايير (ISO) لتقديم إطار هيكلي موحد يتيح لمختلف مصنعي عتاد وبرمجيات الشبكات التخاطب بمرونة وموثوقية. يتألف النموذج من 7 طبقات هرمية متكاملة، لكل منها وظيفة هندسية محددة ووحدة بيانات خاصة:
    </p>

    <div class="layers-grid">

        <!-- الطبقة 7 -->
        <div class="layer-card layer-7">
            <div class="layer-header">
                <div class="layer-badge-group">
                    <span class="layer-num-badge">L7</span>
                    <div>
                        <h4 class="layer-title">طبقة التطبيقات (Application Layer)</h4>
                        <span class="layer-role">الواجهة المباشرة التي يتفاعل معها المستخدم وبرامجه لطلب خدمات الشبكة</span>
                    </div>
                </div>
                <span class="layer-pdu-badge pdu-data">PDU: Data</span>
            </div>
            <div class="layer-body">
                <p>تتيح للبرامج (مثل المتصفحات وعملاء البريد وخوادم الملفات) الوصول إلى موارد الشبكة وتقديم الخدمات للمستخدم النهائي مباشرة.</p>
                <div class="layer-details-row">
                    <div class="layer-detail-item">
                        <strong>الأجهزة العاملة:</strong> الحواسيب، خوادم الويب، الجدران النارية الذكية (NGFW Layer 7).
                    </div>
                    <div class="layer-detail-item">
                        <strong>البروتوكولات الشائعة:</strong>
                        <div class="tech-chips-list">
                            <span class="tech-chip">HTTP / HTTPS (80/443)</span>
                            <span class="tech-chip">DNS (53)</span>
                            <span class="tech-chip">DHCP (67/68)</span>
                            <span class="tech-chip">SSH (22)</span>
                            <span class="tech-chip">FTP (20/21)</span>
                            <span class="tech-chip">SMTP (25/587)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الطبقة 6 -->
        <div class="layer-card layer-6">
            <div class="layer-header">
                <div class="layer-badge-group">
                    <span class="layer-num-badge">L6</span>
                    <div>
                        <h4 class="layer-title">طبقة التقديم (Presentation Layer)</h4>
                        <span class="layer-role">المترجم التقني: التشفير، الضغط، وترجمة تنسيقات وتوافق البيانات</span>
                    </div>
                </div>
                <span class="layer-pdu-badge pdu-data">PDU: Data</span>
            </div>
            <div class="layer-body">
                <p>تضمن أن البيانات المرسلة من طبقة التطبيقات في جهاز ما يمكن قراءتها وفهمها بواسطة طبقة التطبيقات في جهاز آخر، بغض النظر عن نظام التشغيل أو المعمارية.</p>
                <div class="layer-details-row">
                    <div class="layer-detail-item">
                        <strong>الوظائف الجوهرية:</strong> تشفير البيانات وفك التشفير، ضغط البيانات لتسريع النقل، تحويل الترميز.
                    </div>
                    <div class="layer-detail-item">
                        <strong>المعايير والتنسيقات:</strong>
                        <div class="tech-chips-list">
                            <span class="tech-chip">SSL / TLS 1.3</span>
                            <span class="tech-chip">AES-256</span>
                            <span class="tech-chip">JSON / XML</span>
                            <span class="tech-chip">ASCII / Unicode</span>
                            <span class="tech-chip">JPEG / PNG / MP4</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الطبقة 5 -->
        <div class="layer-card layer-5">
            <div class="layer-header">
                <div class="layer-badge-group">
                    <span class="layer-num-badge">L5</span>
                    <div>
                        <h4 class="layer-title">طبقة الجلسة (Session Layer)</h4>
                        <span class="layer-role">إدارة دورة حياة الاتصال: الإنشاء، المزامنة، والتفاوض والإنهاء</span>
                    </div>
                </div>
                <span class="layer-pdu-badge pdu-data">PDU: Data</span>
            </div>
            <div class="layer-body">
                <p>مسؤولة عن تأسيس وحفظ ومزامنة جلسات الحوار بين التطبيقات على طرفي الاتصال، وتحديد وضع الإرسال (Simplex, Half-Duplex, Full-Duplex).</p>
                <div class="layer-details-row">
                    <div class="layer-detail-item">
                        <strong>الآليات الهندسية:</strong> نقاط التفتيش (Checkpoints) لاستئناف النقل عند انقطاع الاتصال دون إعادة إرسال الملف كاملاً.
                    </div>
                    <div class="layer-detail-item">
                        <strong>البروتوكولات:</strong>
                        <div class="tech-chips-list">
                            <span class="tech-chip">RPC (Remote Procedure Call)</span>
                            <span class="tech-chip">NetBIOS</span>
                            <span class="tech-chip">SIP (Session Initiation)</span>
                            <span class="tech-chip">Sockets API</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الطبقة 4 -->
        <div class="layer-card layer-4">
            <div class="layer-header">
                <div class="layer-badge-group">
                    <span class="layer-num-badge">L4</span>
                    <div>
                        <h4 class="layer-title">طبقة النقل (Transport Layer)</h4>
                        <span class="layer-role">النقل الشامل بين التطبيقات (End-to-End Delivery) والعنونة عبر أرقام المنافذ (Ports)</span>
                    </div>
                </div>
                <span class="layer-pdu-badge pdu-segment">PDU: Segment</span>
            </div>
            <div class="layer-body">
                <p>تتحكم في تدفق البيانات، وإعادة التجميع، واكتشاف الأخطاء، وإعادة الإرسال. تُميز التطبيقات من خلال أرقام المنافذ (Port Numbers: 1 - 65,535).</p>
                <div class="layer-details-row">
                    <div class="layer-detail-item">
                        <strong>مقارنة جوهرية (TCP vs UDP):</strong>
                        <ul style="margin:4px 0 0; padding-right:18px; font-size:0.92rem;">
                            <li><strong>TCP:</strong> موثوق، يعتمد على المصافحة الثلاثية (3-Way Handshake: SYN, SYN-ACK, ACK)، وإعادة إرسال الحزم المفقودة، والتحكم بالتدفق (Flow Control عبر Windowing).</li>
                            <li><strong>UDP:</strong> سريع، خفيف، عديم الاتصال (Best-Effort)، مناسب للصوت والفيديو والبث المباشر وألعاب الفيديو و DNS.</li>
                        </ul>
                    </div>
                    <div class="layer-detail-item">
                        <strong>البروتوكولات:</strong>
                        <div class="tech-chips-list">
                            <span class="tech-chip">TCP (Transmission Control)</span>
                            <span class="tech-chip">UDP (User Datagram)</span>
                            <span class="tech-chip">Port Numbers (0–65535)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الطبقة 3 -->
        <div class="layer-card layer-3">
            <div class="layer-header">
                <div class="layer-badge-group">
                    <span class="layer-num-badge">L3</span>
                    <div>
                        <h4 class="layer-title">طبقة الشبكة (Network Layer)</h4>
                        <span class="layer-role">العنونة المنطقية (Logical IP Addressing) وتوجيه المسارات (Routing & Path Selection)</span>
                    </div>
                </div>
                <span class="layer-pdu-badge pdu-packet">PDU: Packet</span>
            </div>
            <div class="layer-body">
                <p>تحدد المسار الأفضل والأنسب لنقل الحزم بين شبكات مختلفة غير متصلة مباشرة، وتستخدم العناوين المنطقية مثل IPv4 و IPv6.</p>
                <div class="layer-details-row">
                    <div class="layer-detail-item">
                        <strong>الأجهزة العاملة:</strong> الموجهات (Routers)، ومبدلات الطبقة الثالثة (Layer 3 Multilayer Switches).
                    </div>
                    <div class="layer-detail-item">
                        <strong>البروتوكولات:</strong>
                        <div class="tech-chips-list">
                            <span class="tech-chip">IPv4 (32-bit)</span>
                            <span class="tech-chip">IPv6 (128-bit)</span>
                            <span class="tech-chip">ICMP (Ping/Traceroute)</span>
                            <span class="tech-chip">OSPF / EIGRP / BGP</span>
                            <span class="tech-chip">IPsec</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الطبقة 2 -->
        <div class="layer-card layer-2">
            <div class="layer-header">
                <div class="layer-badge-group">
                    <span class="layer-num-badge">L2</span>
                    <div>
                        <h4 class="layer-title">طبقة ربط البيانات (Data Link Layer)</h4>
                        <span class="layer-role">العنونة الفيزيائية (MAC Addresses)، تأطير الحزم (Framing)، واكتشاف أخطاء الوسط</span>
                    </div>
                </div>
                <span class="layer-pdu-badge pdu-frame">PDU: Frame</span>
            </div>
            <div class="layer-body">
                <p>تنظم كيفية الوصول إلى الوسط الناقل للشبكة الفيزيائية وتتعامل مع العناوين الفيزيائية المضمنة في بطاقات الشبكة (MAC Address: 48-bit). تنقسم إلى طبقتين فرعيتين: LLC (Logical Link Control) و MAC (Media Access Control).</p>
                <div class="layer-details-row">
                    <div class="layer-detail-item">
                        <strong>الأجهزة العاملة:</strong> مبدلات الشبكة (Switches)، الجسور (Bridges)، بطاقات الشبكة (NICs)، نقاط الوصول اللاسلكية (WAPs).
                    </div>
                    <div class="layer-detail-item">
                        <strong>التقنيات والمعايير:</strong>
                        <div class="tech-chips-list">
                            <span class="tech-chip">Ethernet (IEEE 802.3)</span>
                            <span class="tech-chip">Wi-Fi (IEEE 802.11)</span>
                            <span class="tech-chip">FCS / CRC-32 (Error Check)</span>
                            <span class="tech-chip">MAC Address (48-bit Hex)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- الطبقة 1 -->
        <div class="layer-card layer-1">
            <div class="layer-header">
                <div class="layer-badge-group">
                    <span class="layer-num-badge">L1</span>
                    <div>
                        <h4 class="layer-title">الطبقة الفيزيائية (Physical Layer)</h4>
                        <span class="layer-role">النبض الكهربائي والضوئي: تحويل البتات الرقمية (0 و 1) إلى إشارات وسيطة</span>
                    </div>
                </div>
                <span class="layer-pdu-badge pdu-bits">PDU: Bits</span>
            </div>
            <div class="layer-body">
                <p>تتعامل مع الخصائص الميكانيكية والكهربائية والوظيفية لوسائط الإرسال، وتحدد سرعة نقل البيانات بالبت في الثانية، وأنواع الموصلات والأسلاك وتزامن النبضات.</p>
                <div class="layer-details-row">
                    <div class="layer-detail-item">
                        <strong>الأجهزة والوسائط:</strong> كوابل النحاس الملتوية UTP/STP (Cat5e, Cat6, Cat6a)، كوابل الألياف الضوئية (Single-Mode / Multi-Mode Fiber)، المكررات (Repeaters)، الموزعات القديمة (Hubs).
                    </div>
                    <div class="layer-detail-item">
                        <strong>المعايير والموصلات:</strong>
                        <div class="tech-chips-list">
                            <span class="tech-chip">RJ-45 Connector</span>
                            <span class="tech-chip">LC / SC Fiber Connectors</span>
                            <span class="tech-chip">SFP / SFP+ Transceivers</span>
                            <span class="tech-chip">1000BASE-T / 10GBASE-SR</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- القسم الثاني: تشريح الحزمة والتغليف -->
    <h3 id="sec-encapsulation"><i class="fa-solid fa-box-open"></i> 2. تشريح حزمة البيانات وعملية التغليف (Wireshark Packet Anatomy & Encapsulation Flow)</h3>
    <p>
        عند إرسال بيانات من جهاز لآخر (مثل طلب صفحة ويب)، تمر البيانات بعملية <strong>التغليف (Encapsulation)</strong> نزولاً من الطبقة 7 إلى 1، حيث تضيف كل طبقة ترويسة تحكم (Header) خاصة بها. وعند وصول الإشارة لجهاز الهدف، تحدث عملية <strong>فك التغليف (De-encapsulation)</strong> صعوداً من 1 إلى 7:
    </p>

    <!-- محاكي تشريح الحزمة البصري Realistic Packet Anatomy Inspector -->
    <div class="packet-inspector-container">
        <div class="packet-inspector-header">
            <span><i class="fa-solid fa-microchip"></i> تشريح إطار الإيثرنت القياسي (Ethernet II Frame Anatomy)</span>
            <span class="badge badge-secondary" style="font-family:var(--font-mono);">MTU: 1500 Bytes</span>
        </div>
        <div class="packet-frame-visual">
            <div class="packet-block block-frame" title="Layer 2 Header">
                <div class="block-title">Ethernet Header (L2)</div>
                <div class="block-bytes">14 Bytes</div>
                <div class="block-desc">Dest MAC (6B) + Src MAC (6B) + EtherType 0x0800 (2B)</div>
            </div>
            <div class="packet-block block-ip" title="Layer 3 Header">
                <div class="block-title">IP Header (L3)</div>
                <div class="block-bytes">20 Bytes</div>
                <div class="block-desc">Src IP (4B) + Dest IP (4B) + TTL + Protocol 6 (TCP)</div>
            </div>
            <div class="packet-block block-tcp" title="Layer 4 Header">
                <div class="block-title">TCP Header (L4)</div>
                <div class="block-bytes">20 Bytes</div>
                <div class="block-desc">Src Port (2B) + Dst Port 443 (2B) + Seq/Ack Num + Flags</div>
            </div>
            <div class="packet-block block-payload" title="Layer 7-5 Data Payload">
                <div class="block-title">Application Data (L7 Payload)</div>
                <div class="block-bytes">Variable Bytes</div>
                <div class="block-desc">HTTP/HTTPS Payload: GET /index.html HTTP/1.1</div>
            </div>
            <div class="packet-block block-trailer" title="Layer 2 Trailer">
                <div class="block-title">FCS Trailer (L2)</div>
                <div class="block-bytes">4 Bytes</div>
                <div class="block-desc">CRC-32 Checksum لاكتشاف الأخطاء</div>
            </div>
        </div>

        <div class="encapsulation-timeline">
            <div class="timeline-step">
                <div class="step-circle">1</div>
                <div class="step-label"><strong>User Data</strong><br><span class="code-font">Layers 7, 6, 5</span></div>
            </div>
            <div class="timeline-arrow"><i class="fa-solid fa-arrow-left"></i></div>
            <div class="timeline-step">
                <div class="step-circle">2</div>
                <div class="step-label"><strong>Segment</strong><br><span class="code-font">+ TCP/UDP Header</span></div>
            </div>
            <div class="timeline-arrow"><i class="fa-solid fa-arrow-left"></i></div>
            <div class="timeline-step">
                <div class="step-circle">3</div>
                <div class="step-label"><strong>Packet</strong><br><span class="code-font">+ IP Header</span></div>
            </div>
            <div class="timeline-arrow"><i class="fa-solid fa-arrow-left"></i></div>
            <div class="timeline-step">
                <div class="step-circle">4</div>
                <div class="step-label"><strong>Frame</strong><br><span class="code-font">+ MAC & FCS Trailer</span></div>
            </div>
            <div class="timeline-arrow"><i class="fa-solid fa-arrow-left"></i></div>
            <div class="timeline-step">
                <div class="step-circle">5</div>
                <div class="step-label"><strong>Bits (0101)</strong><br><span class="code-font">Physical Transmission</span></div>
            </div>
        </div>
    </div>

    <!-- القسم الثالث: مقارنة الأجهزة والمجالات -->
    <h3 id="sec-domains"><i class="fa-solid fa-network-wired"></i> 3. هندسة مجالات التصادم (Collision Domains) ومجالات البث (Broadcast Domains)</h3>
    <p>
        من أهم ركائز اختبار CCNA وهندسة الشبكات العملية فهم الفارق بين أجهزة الطبقة 1 و 2 و 3 في التعامل مع تدفق البيانات:
    </p>

    <table class="academic-table">
        <thead>
            <tr>
                <th>الجهاز (Device)</th>
                <th>طبقة العمل</th>
                <th>مجال التصادم (Collision Domain)</th>
                <th>مجال البث (Broadcast Domain)</th>
                <th>سلوك معالجة الإشارات</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Hub (الموزع القديم)</strong></td>
                <td><span class="badge badge-secondary">Layer 1</span></td>
                <td>مجال تصادم واحد مشترك لكافة المنافذ (تصادمات متكررة)</td>
                <td>مجال بث واحد مشترك لكافة المنافذ</td>
                <td>يكرر الإشارة كهربائياً لجميع المنافذ دون فحص أي عناوين (Dumb Repeater).</td>
            </tr>
            <tr>
                <td><strong>Switch (المبدل الذكي)</strong></td>
                <td><span class="badge badge-primary">Layer 2</span></td>
                <td><strong>كل منفذ يمثل مجال تصادم مستقل تماماً (Micro-segmentation)</strong></td>
                <td>مجال بث واحد لجميع المنافذ (إلا عند تقسيم VLANs)</td>
                <td>يتعلم عناوين MAC ديناميكياً ويبني جدول <code>MAC Table</code> ويوجه الإطار للمنفذ المعني فقط.</td>
            </tr>
            <tr>
                <td><strong>Router (الموجه)</strong></td>
                <td><span class="badge badge-success">Layer 3</span></td>
                <td>كل منفذ يمثل مجال تصادم مستقل</td>
                <td><strong>كل منفذ يكسر ويفصل مجالات البث (Breaks Broadcasts)</strong></td>
                <td>لا يمرر حزم البث العام (Broadcasts) ويختار المسار بناءً على جدول التوجيه <code>Routing Table</code>.</td>
            </tr>
        </tbody>
    </table>

    <!-- القسم الرابع: أوامر سيسكو الحقيقية CLI -->
    <h3 id="sec-cisco-cli"><i class="fa-solid fa-terminal"></i> 4. موجه أوامر سيسكو الحقيقي (Cisco IOS Verification Commands)</h3>
    <p>
        في بيئات العمل والإنتاج الحقيقية، يستخدم مهندس الشبكات هذه الأوامر على أجهزة Cisco Catalyst Switch للتحقق من الطبقات ومنافذ الشبكة:
    </p>

    <div class="cisco-terminal-box">
        <div class="terminal-bar">
            <div class="terminal-dots">
                <span class="dot-red"></span>
                <span class="dot-yellow"></span>
                <span class="dot-green"></span>
            </div>
            <div class="terminal-title"><i class="fa-solid fa-terminal"></i> Cisco Catalyst 2960 Switch — IOS CLI Terminal</div>
            <button type="button" class="cli-copy-btn" onclick="copyTerminalText('cli-block-1')">
                <i class="fa-regular fa-copy"></i> نسخ الأوامر
            </button>
        </div>
        <pre class="terminal-content" id="cli-block-1"><span class="cli-prompt">Switch#</span> <span class="cli-cmd">show interfaces gigabitEthernet 0/1 status</span>
Port      Name               Status       Vlan       Duplex  Speed Type
Gi0/1     Uplink-To-Router   <span class="cli-highlight-green">connected</span>    trunk      <span class="cli-highlight-cyan">a-full</span>  <span class="cli-highlight-cyan">a-1000</span> 10/100/1000BaseTX

<span class="cli-prompt">Switch#</span> <span class="cli-cmd">show mac address-table dynamic</span>
          Mac Address Table
-------------------------------------------
Vlan    Mac Address       Type        Ports
----    -----------       --------    -----
   1    <span class="cli-highlight-cyan">0050.7966.6800</span>    DYNAMIC     Gi0/1
  10    <span class="cli-highlight-cyan">c472.9521.aa01</span>    DYNAMIC     Fa0/2
  10    <span class="cli-highlight-cyan">c472.9521.aa02</span>    DYNAMIC     Fa0/3
Total Mac Addresses for this criterion: 3

<span class="cli-prompt">Switch#</span> <span class="cli-cmd">show version | include Model</span>
Model number                    : WS-C2960-24TT-L</pre>
    </div>

    <!-- القسم الخامس: نصائح اختبار CCNA وحقائق بيئات العمل -->
    <h3 id="sec-exam-tips"><i class="fa-solid fa-shield-halved"></i> 5. نصائح اختبار سيسكو CCNA 200-301 وحقائق بيئات الإنتاج</h3>

    <div class="exam-tips-grid">
        <div class="exam-tip-card">
            <div class="exam-tip-header">
                <i class="fa-solid fa-lightbulb" style="color:#F59E0B;"></i>
                <h4>فخ شهير في اختبار CCNA 200-301</h4>
            </div>
            <p>
                إذا سألك الاختبار: <em>"كم عدد مجالات التصادم ومجالات البث في شبكة تحتوي على Router بمخرجين، متصل بكل مخرج Switch بـ 8 منافذ؟"</em><br>
                <strong>الإجابة الهندسية:</strong><br>
                • عدد مجالات البث = <strong>2</strong> (لأن الراوتر يفصل البث وكل منفذ في الراوتر يشكل مجال بث مستقل).<br>
                • عدد مجالات التصادم = <strong>16</strong> (كل منفذ في السويتش يشكل مجال تصادم مستقل تماماً).
            </p>
        </div>

        <div class="production-gotcha-card">
            <div class="production-gotcha-header">
                <i class="fa-solid fa-triangle-exclamation" style="color:#E11D48;"></i>
                <h4>واقع بيئات العمل والشركات (Production Gotcha)</h4>
            </div>
            <p>
                احذر من مشكلة <strong>Duplex Mismatch</strong>! إذا تم ضبط أحد طرفي الكابل على <em>Full-Duplex</em> والطرف الآخر على <em>Half-Duplex</em>، سيعمل الاتصال ظاهرياً ولكن سيحدث هدر كبير للبيانات وتراكم أخطاء <strong>CRC / Late Collisions</strong> عند فحص <code>show interfaces</code>؛ مما يتسبب في بطء فادح للشبكة.
            </p>
        </div>
    </div>

</div>
HTML;

$module2_html = <<<'HTML'
<div class="study-guide-content">

    <!-- شريط أهداف التعلم -->
    <div class="callout-box" id="sec-overview">
        <h4><i class="fa-solid fa-graduation-cap"></i> أهداف الدرس ومعايير إتقان الـ Subnetting (CCNA Mastery):</h4>
        <p>
            1. <strong>الهندسة الرياضية:</strong> إتقان استعارة البتات واستخراج أقنعة الشبكة والـ Wildcard Mask.<br>
            2. <strong>طريقة سيسكو السريعة (Magic Number):</strong> حساب عناوين الشبكة والبث ونطاق الأجهزة الصالحة في ثوانٍ معدودة.<br>
            3. <strong>حالات CIDR الحدية:</strong> فهم معايير RFC لـ /30 و /31 و /32 واستخداماتها في الروابط النقطية.<br>
            4. <strong>عزل الشبكات الافتراضية (VLANs & Trunking):</strong> فهم بروتوكول IEEE 802.1Q وعنونة Access vs Trunk Ports.<br>
            5. <strong>أوامر التحقق في Cisco IOS:</strong> ممارسة أوامر <code>show ip interface brief</code> و <code>show vlan brief</code>.
        </p>
    </div>

    <!-- القسم الأول: القواعد الرياضية -->
    <h3 id="sec-subnetting-math"><i class="fa-solid fa-calculator"></i> 1. الهندسة الرياضية لتقسيم الشبكات (IPv4 Subnetting & Magic Number Method)</h3>
    <p>
        يتألف عنوان IPv4 من <strong>32 بت</strong> مقسمة إلى 4 خانات ثمانية (Octets). وتحدد بادئة الشبكة <strong>CIDR (Classless Inter-Domain Routing)</strong> الحد الفاصل بين بتات الشبكة وبتات الأجهزة:
    </p>

    <div class="formula-cards-grid">
        <div class="formula-card">
            <div class="formula-icon"><i class="fa-solid fa-sitemap"></i></div>
            <div class="formula-title">عدد الشبكات الفرعية (Subnets)</div>
            <div class="formula-math">2<sup>n</sup></div>
            <div class="formula-desc">حيث n = عدد البتات المقترضة للشبكة من خانة المضيف</div>
        </div>

        <div class="formula-card">
            <div class="formula-icon"><i class="fa-solid fa-laptop"></i></div>
            <div class="formula-title">عدد الأجهزة الصالحة (Usable Hosts)</div>
            <div class="formula-math">2<sup>h</sup> - 2</div>
            <div class="formula-desc">حيث h = بتات المضيف المتبقية (نطرح عنوان الشبكة وعنوان البث)</div>
        </div>

        <div class="formula-card">
            <div class="formula-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
            <div class="formula-title">رقم القفزة السحري (Magic Number)</div>
            <div class="formula-math">256 - Mask</div>
            <div class="formula-desc">حيث Mask = قيمة الثماني المتغير في قناع الشبكة</div>
        </div>

        <div class="formula-card">
            <div class="formula-icon"><i class="fa-solid fa-mask"></i></div>
            <div class="formula-title">قناع البدل (Wildcard Mask)</div>
            <div class="formula-math">255.255.255.255 - Mask</div>
            <div class="formula-desc">يستخدم في قوائم التحكم بالوصول ACL وبروتوكول OSPF</div>
        </div>
    </div>

    <!-- مثال تطبيقي محلول -->
    <h4 style="margin-top:24px; color:var(--brand-primary);"><i class="fa-solid fa-lightbulb"></i> مثال تطبيقي محلول خطوة بخطوة: شبكة <code>192.168.10.0/26</code></h4>
    <div class="solved-example-box">
        <div class="solved-step">
            <span class="step-num">1</span>
            <div><strong>تحديد البادئة وقناع الشبكة:</strong> <code>/26</code> يعني أن أول 26 بت هي 1، وآخر 6 بتات هي 0. في الثماني الأخير: <code>11000000 = 128 + 64 = 192</code>. القناع هو <code>255.255.255.192</code>.</div>
        </div>
        <div class="solved-step">
            <span class="step-num">2</span>
            <div><strong>حساب رقم القفزة (Magic Number):</strong> <code>256 - 192 = 64</code>. تبدأ كل شبكة بمضاعفات الـ 64 (0, 64, 128, 192).</div>
        </div>
        <div class="solved-step">
            <span class="step-num">3</span>
            <div><strong>عدد الأجهزة لكل شبكة:</strong> بتات المضيف h = 6. عدد الأجهزة الصالحة = <code>2^6 - 2 = 64 - 2 = 62 جهازاً</code>.</div>
        </div>
        <div class="solved-step">
            <span class="step-num">4</span>
            <div><strong>قناع البدل (Wildcard Mask):</strong> <code>255.255.255.255 - 255.255.255.192 = 0.0.0.63</code>.</div>
        </div>
    </div>

    <!-- القسم الثاني: جدول CIDR والحالات الطرفية -->
    <h3 id="sec-cidr-table"><i class="fa-solid fa-table"></i> 2. جدول البادئات المرجعي والمعايير الحدية (Edge Cases Table)</h3>
    <table class="academic-table">
        <thead>
            <tr>
                <th>البادئة (CIDR)</th>
                <th>قناع الشبكة (Subnet Mask)</th>
                <th>رقم القفزة</th>
                <th>الأجهزة الصالحة</th>
                <th>الاستخدام والمعيار الهندسي</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>/24</code></td>
                <td>255.255.255.0</td>
                <td>256</td>
                <td>254 جهاز</td>
                <td>الشبكات المحلية التقليدية للمكاتب والأقسام (LANs).</td>
            </tr>
            <tr>
                <td><code>/27</code></td>
                <td>255.255.255.224</td>
                <td>32</td>
                <td>30 جهاز</td>
                <td>أقسام الشركات الصغيرة أو شبكات الخوادم الفرعية.</td>
            </tr>
            <tr>
                <td><code>/30</code></td>
                <td>255.255.255.252</td>
                <td>4</td>
                <td><strong>2 جهازين</strong></td>
                <td>الروابط النقطية التقليدية Point-to-Point بين الموجهات والراوترات.</td>
            </tr>
            <tr>
                <td><code>/31</code></td>
                <td>255.255.255.254</td>
                <td>2</td>
                <td><strong>2 جهازين (RFC 3021)</strong></td>
                <td>معيار حديث لتوفير العناوين في الروابط النقطية؛ حيث لا يُهدر أي عنوان للشبكة أو البث.</td>
            </tr>
            <tr>
                <td><code>/32</code></td>
                <td>255.255.255.255</td>
                <td>1</td>
                <td>1 عنوان مفرد</td>
                <td>تحديد مسار جهاز محدد (Host Route) أو واجهات وهمية مثل Loopback Interface.</td>
            </tr>
            <tr>
                <td><code>/0</code></td>
                <td>0.0.0.0</td>
                <td>-</td>
                <td>الإنترنت بأكمله</td>
                <td>المسار الافتراضي للإنترنت (Default Route: <code>0.0.0.0 0.0.0.0</code>).</td>
            </tr>
        </tbody>
    </table>

    <!-- القسم الثالث: VLANs & Trunking -->
    <h3 id="sec-vlans-trunking"><i class="fa-solid fa-diagram-project"></i> 3. شبكات الـ VLANs وبروتوكول 802.1Q Trunking</h3>
    <p>
        تسمح <strong>VLAN (Virtual Local Area Network)</strong> بتقسيم مبدل الشبكة (Switch) فيزيائياً إلى عدة شبكات منطقية معزولة، حيث يمثل كل VLAN مجال بث (Broadcast Domain) مستقل تماماً:
    </p>

    <div class="vlan-anatomy-grid">
        <div class="vlan-concept-card">
            <h4><i class="fa-solid fa-plug"></i> منافذ الوصول (Access Ports)</h4>
            <p>تتصل بالأجهزة الطرفية (PCs, Printers, Servers) وتنتمي لـ VLAN واحد فقط. لا تحتوي الإطارات على أي Tag عند خروجها إلى الجهاز.</p>
        </div>

        <div class="vlan-concept-card">
            <h4><i class="fa-solid fa-bridge"></i> منافذ الجذع (Trunk Ports)</h4>
            <p>تربط بين السويتشات أو بين السويتش والراوتر (Router-on-a-Stick). تمرر حركة مرور عدة VLANs عبر إضافة علامة <strong>802.1Q Tag (4 Bytes)</strong> لكل إطار.</p>
        </div>

        <div class="vlan-concept-card">
            <h4><i class="fa-solid fa-shield"></i> الشبكة الأصلية (Native VLAN)</h4>
            <p>الـ VLAN الوحيد الذي تمر إطاراته عبر خط الـ Trunk <strong>بدون أي علامة Tag</strong> (افتراضياً VLAN 1). يُنصح أمنياً بتغييره لمنع هجمات VLAN Hopping.</p>
        </div>
    </div>

    <!-- القسم الرابع: أوامر سيسكو CLI -->
    <h3 id="sec-cisco-cli"><i class="fa-solid fa-terminal"></i> 4. موجه أوامر سيسكو لتكوين والتحقق من VLANs و Subnetting</h3>
    <div class="cisco-terminal-box">
        <div class="terminal-bar">
            <div class="terminal-dots">
                <span class="dot-red"></span>
                <span class="dot-yellow"></span>
                <span class="dot-green"></span>
            </div>
            <div class="terminal-title"><i class="fa-solid fa-terminal"></i> Cisco Catalyst Switch — VLAN & Interface Verification</div>
            <button type="button" class="cli-copy-btn" onclick="copyTerminalText('cli-block-2')">
                <i class="fa-regular fa-copy"></i> نسخ الأوامر
            </button>
        </div>
        <pre class="terminal-content" id="cli-block-2"><span class="cli-prompt">Switch#</span> <span class="cli-cmd">configure terminal</span>
<span class="cli-prompt">Switch(config)#</span> <span class="cli-cmd">vlan 20</span>
<span class="cli-prompt">Switch(config-vlan)#</span> <span class="cli-cmd">name HR-Department</span>
<span class="cli-prompt">Switch(config-vlan)#</span> <span class="cli-cmd">interface FastEthernet 0/5</span>
<span class="cli-prompt">Switch(config-if)#</span> <span class="cli-cmd">switchport mode access</span>
<span class="cli-prompt">Switch(config-if)#</span> <span class="cli-cmd">switchport access vlan 20</span>
<span class="cli-prompt">Switch(config-if)#</span> <span class="cli-cmd">interface GigabitEthernet 0/1</span>
<span class="cli-prompt">Switch(config-if)#</span> <span class="cli-cmd">switchport mode trunk</span>
<span class="cli-prompt">Switch(config-if)#</span> <span class="cli-cmd">switchport trunk native vlan 99</span>
<span class="cli-prompt">Switch(config-if)#</span> <span class="cli-cmd">end</span>

<span class="cli-prompt">Switch#</span> <span class="cli-cmd">show vlan brief</span>
VLAN Name                             Status    Ports
---- -------------------------------- --------- -------------------------------
1    default                          active    Fa0/1, Fa0/2, Fa0/3, Fa0/4
20   <span class="cli-highlight-cyan">HR-Department</span>                    <span class="cli-highlight-green">active</span>    Fa0/5
99   <span class="cli-highlight-cyan">Management-Native</span>                <span class="cli-highlight-green">active</span>    </pre>
    </div>

    <!-- القسم الخامس: نصائح اختبار CCNA -->
    <h3 id="sec-exam-tips"><i class="fa-solid fa-shield-halved"></i> 5. نصائح اختبار سيسكو CCNA 200-301 وحقائق بيئات العمل</h3>
    <div class="exam-tips-grid">
        <div class="exam-tip-card">
            <div class="exam-tip-header">
                <i class="fa-solid fa-lightbulb" style="color:#F59E0B;"></i>
                <h4>فخ اختبار سيسكو في عناوين الـ Usable Hosts</h4>
            </div>
            <p>
                لا تنسَ أبداً طرح <strong>2</strong> عند حساب الأجهزة الصالحة للاستخدام! إذا كانت البادئة <code>/28</code> (4 بتات للمضيف)، فإن عدد العناوين الإجمالي هو 16، لكن عدد الأجهزة المتاحة هو <strong>14 فقط</strong>. إذا سألك السؤال عن "كم عنوان IP يمكن إسناده لموظفي القسم؟" فاختر دائماً 14 وليس 16.
            </p>
        </div>

        <div class="production-gotcha-card">
            <div class="production-gotcha-header">
                <i class="fa-solid fa-triangle-exclamation" style="color:#E11D48;"></i>
                <h4>Native VLAN Mismatch في بيئات الإنتاج</h4>
            </div>
            <p>
                إذا كان السويتش الأول مضبوطاً على <code>Native VLAN 99</code> والطرف الآخر من كابل الـ Trunk مضبوطاً على <code>Native VLAN 1</code>، ستشهد سجلات السويتش سيل رسائل خطأ من بروتوكول CDP باسم <strong>Native VLAN Mismatch</strong>، وقد يؤدي ذلك لدمج مجالات البث والتسبب في ثغرات أمنية وحلقات توجيه!
            </p>
        </div>
    </div>

</div>
HTML;

$module3_html = <<<'HTML'
<div class="study-guide-content">

    <!-- شريط أهداف التعلم -->
    <div class="callout-box" id="sec-overview">
        <h4><i class="fa-solid fa-graduation-cap"></i> أهداف مسار التوجيه وهندسة التبديل المتقدمة (CCNA Routing & STP):</h4>
        <p>
            1. <strong>المسافات الإدارية (Administrative Distance):</strong> حفظ وتطبيق جدول AD لمفاضلة مصادر التوجيه المختلفة.<br>
            2. <strong>معمارية بروتوكول OSPF:</strong> فهم خوارزمية Dijkstra، ومراحل الجوار السبع، وأهمية منطقة العمود الفقري Area 0.<br>
            3. <strong>حساب التكلفة في OSPF:</strong> تطبيق معادلة Cost ومواءمة الـ Reference Bandwidth لشبكات الألياف الحديثة.<br>
            4. <strong>بروتوكول الشجرة الممتدة (STP 802.1D & RSTP):</strong> إتقان انتخاب الـ Root Bridge وحالات المنافذ ومنع الحلقات.<br>
            5. <strong>أوامر التحقق في Cisco IOS:</strong> قراءة جدول التوجيه <code>show ip route</code> وجيران OSPF <code>show ip ospf neighbor</code>.
        </p>
    </div>

    <!-- القسم الأول: جدول المسافات الإدارية -->
    <h3 id="sec-admin-distance"><i class="fa-solid fa-compass"></i> 1. جدول المسافات الإدارية القياسي (Administrative Distance Matrix)</h3>
    <p>
        المسافة الإدارية <strong>(Administrative Distance - AD)</strong> هي رقم يُحدد مدى مصداقية وجدارة مصدر المسار في راوترات سيسكو. <strong>كلما كان الرقم أقل، كان المسار أكثر ثقة وأولوية للظهور في جدول التوجيه:</strong>
    </p>

    <table class="academic-table">
        <thead>
            <tr>
                <th>مصدر المسار (Route Source)</th>
                <th>رمز المسار (Code)</th>
                <th>المسافة الإدارية (AD)</th>
                <th>طبيعة المسار وسلوكه الهندسي</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Connected Interface</strong></td>
                <td><span class="badge badge-success">C</span></td>
                <td><strong class="code-font" style="font-size:1.1rem; color:#059669;">0</strong></td>
                <td>مسار متصل فيزيائياً ومباشراً بمنفذ الراوتر النشط (الأعلى موثوقية مطلقاً).</td>
            </tr>
            <tr>
                <td><strong>Static Route</strong></td>
                <td><span class="badge badge-primary">S</span></td>
                <td><strong class="code-font" style="font-size:1.1rem; color:#5B4FE8;">1</strong></td>
                <td>مسار ثابت يدوي قام مهندس الشبكة بتعريفه صراحة.</td>
            </tr>
            <tr>
                <td><strong>External BGP (eBGP)</strong></td>
                <td><span class="badge badge-secondary">B</span></td>
                <td><strong class="code-font" style="font-size:1.1rem;">20</strong></td>
                <td>توجيه بين أنظمة مستقلة AS مختلفة عبر مزودي الإنترنت العالمية.</td>
            </tr>
            <tr>
                <td><strong>EIGRP (Internal)</strong></td>
                <td><span class="badge badge-warning">D</span></td>
                <td><strong class="code-font" style="font-size:1.1rem;">90</strong></td>
                <td>بروتوكول سيسكو الهجين المتطور المعتمد على خوارزمية DUAL.</td>
            </tr>
            <tr>
                <td><strong>OSPF</strong></td>
                <td><span class="badge badge-primary">O</span></td>
                <td><strong class="code-font" style="font-size:1.1rem; color:#5B4FE8;">110</strong></td>
                <td>بروتوكول حالة الرابط المعياري المفتوح الأكثر استخداماً عالمياً.</td>
            </tr>
            <tr>
                <td><strong>IS-IS</strong></td>
                <td><span class="badge badge-secondary">i</span></td>
                <td><strong class="code-font" style="font-size:1.1rem;">115</strong></td>
                <td>بروتوكول حالة الرابط المستخدم في شبكات مزودي الخدمة (Service Providers).</td>
            </tr>
            <tr>
                <td><strong>RIP</strong></td>
                <td><span class="badge badge-danger">R</span></td>
                <td><strong class="code-font" style="font-size:1.1rem; color:#E11D48;">120</strong></td>
                <td>بروتوكول شعاع المسافة القديم (يعتمد على عدد القفزات Hop Count كحد أقصى 15).</td>
            </tr>
            <tr>
                <td><strong>Internal BGP (iBGP)</strong></td>
                <td><span class="badge badge-secondary">B</span></td>
                <td><strong class="code-font" style="font-size:1.1rem;">200</strong></td>
                <td>توجيه BGP داخل النظام المستقل نفسه.</td>
            </tr>
        </tbody>
    </table>

    <div class="callout-box" style="margin-top:16px;">
        <strong><i class="fa-solid fa-star"></i> القاعدة الذهبية لجدول توجيه سيسكو (Route Selection Rule):</strong>
        <ol style="margin:8px 0 0; padding-right:20px;">
            <li>أطول بادئة تطابقاً <strong>(Longest Prefix Match)</strong> تفوز دائماً أولاً (مثال: <code>10.1.1.0/28</code> تفوز على <code>10.1.1.0/24</code> بغض النظر عن البروتوكول).</li>
            <li>إذا تساوت البادئات، يفوز المسار صاحب <strong>أقل مسافة إدارية (Lowest AD)</strong>.</li>
            <li>إذا كان المساران من نفس البروتوكول، يفوز المسار صاحب <strong>أقل مقياس تكلفة (Lowest Metric)</strong>.</li>
        </ol>
    </div>

    <!-- القسم الثاني: معمارية OSPF -->
    <h3 id="sec-ospf-architecture"><i class="fa-solid fa-diagram-nested"></i> 2. معمارية بروتوكول OSPF وحالات الجوار السبع</h3>
    <p>
        يعمل <strong>OSPF (Open Shortest Path First)</strong> في الطبقة الثالثة ويعتمد على خوارزمية <strong>Dijkstra (Shortest Path First - SPF)</strong> لبناء شجرة خالية من الحلقات:
    </p>

    <div class="ospf-states-grid">
        <div class="ospf-state-card">
            <span class="ospf-state-num">1</span>
            <div><strong>Down State:</strong> لم يتم استلام أي حزم Hello من الجار بعد.</div>
        </div>
        <div class="ospf-state-card">
            <span class="ospf-state-num">2</span>
            <div><strong>Init State:</strong> استلم الراوتر حزمة Hello من الجار ولكن معرفه (Router ID) ليس مسجلاً فيها بعد.</div>
        </div>
        <div class="ospf-state-card">
            <span class="ospf-state-num">3</span>
            <div><strong>2-Way State:</strong> اتصال ثنائي الاتجاه؛ يتم انتخاب الـ DR و BDR في الشبكات المشتركة (Multi-access).</div>
        </div>
        <div class="ospf-state-card">
            <span class="ospf-state-num">4</span>
            <div><strong>ExStart State:</strong> تفاوض على أرقام التسلسل وتحديد السيد والتابع (Master / Slave).</div>
        </div>
        <div class="ospf-state-card">
            <span class="ospf-state-num">5</span>
            <div><strong>Exchange State:</strong> تبادل حزم DBD (Database Description) التي تلخص حالة الروابط.</div>
        </div>
        <div class="ospf-state-card">
            <span class="ospf-state-num">6</span>
            <div><strong>Loading State:</strong> إرسال طلبات LSR واستلام تحديثات LSU لمعلومات الروابط المفقودة.</div>
        </div>
        <div class="ospf-state-card state-full">
            <span class="ospf-state-num">7</span>
            <div><strong>Full State (الهدف):</strong> تطابق كامل لقاعدة بيانات حالة الروابط (LSDB) بين الجارين؛ واكتمال الجوار بنجاح!</div>
        </div>
    </div>

    <!-- القسم الثالث: STP -->
    <h3 id="sec-stp-mechanics"><i class="fa-solid fa-sitemap"></i> 3. بروتوكول الشجرة الممتدة (Spanning Tree Protocol - STP 802.1D)</h3>
    <p>
        يقوم بروتوكول <strong>STP</strong> بحجب المنافذ الفائضة بصورة منطقية لمنع <strong>حلقات التبديل (Switching Loops)</strong> وعواصف البث (Broadcast Storms) وازدواج الإطارات (MAC Table Thrashing):
    </p>

    <div class="stp-steps-box">
        <div class="stp-step-item">
            <div class="stp-step-badge">خطوة 1</div>
            <div><strong>انتخاب المبدل الجذري (Root Bridge):</strong> السويتش صاحب <strong>أقل قيمة Bridge ID</strong> (تتكون من Bridge Priority + MAC Address).</div>
        </div>
        <div class="stp-step-item">
            <div class="stp-step-badge">خطوة 2</div>
            <div><strong>تحديد منفذ الجذر (Root Port):</strong> منفذ واحد على كل سويتش غير جذري يمتلك أقل مسار تكلفة نحو الـ Root Bridge.</div>
        </div>
        <div class="stp-step-item">
            <div class="stp-step-badge">خطوة 3</div>
            <div><strong>تحديد المنفذ المعين (Designated Port):</strong> منفذ واحد لكل مقطع شبكي ينقل حركة المرور (جميع منافذ الـ Root Bridge هي دائماً Designated وفي حالة Forwarding).</div>
        </div>
        <div class="stp-step-item">
            <div class="stp-step-badge">خطوة 4</div>
            <div><strong>وضع باقي المنافذ في حالة الحجب (Alternate / Blocking):</strong> تُحجب منطقياً لمنع الحلقات وتتحول للعمل تلقائياً عند انقطاع أحد الروابط الرئيسية.</div>
        </div>
    </div>

    <!-- القسم الرابع: أوامر سيسكو CLI -->
    <h3 id="sec-cisco-cli"><i class="fa-solid fa-terminal"></i> 4. أوامر سيسكو للتحقق من OSPF و STP</h3>
    <div class="cisco-terminal-box">
        <div class="terminal-bar">
            <div class="terminal-dots">
                <span class="dot-red"></span>
                <span class="dot-yellow"></span>
                <span class="dot-green"></span>
            </div>
            <div class="terminal-title"><i class="fa-solid fa-terminal"></i> Cisco Router & Switch — OSPF & Spanning Tree Status</div>
            <button type="button" class="cli-copy-btn" onclick="copyTerminalText('cli-block-3')">
                <i class="fa-regular fa-copy"></i> نسخ الأوامر
            </button>
        </div>
        <pre class="terminal-content" id="cli-block-3"><span class="cli-prompt">Router#</span> <span class="cli-cmd">show ip ospf neighbor</span>
Neighbor ID     Pri   State           Dead Time   Address         Interface
192.168.1.2       1   <span class="cli-highlight-green">FULL/DR</span>         00:00:34    10.0.0.2        GigabitEthernet0/0/0

<span class="cli-prompt">Router#</span> <span class="cli-cmd">show ip route ospf</span>
Codes: O - OSPF, IA - OSPF inter area, E1 - OSPF external type 1
Gateway of last resort is not set

O    <span class="cli-highlight-cyan">192.168.20.0/24</span> [110/<span class="cli-highlight-cyan">2</span>] via 10.0.0.2, 00:14:22, GigabitEthernet0/0/0

<span class="cli-prompt">Switch#</span> <span class="cli-cmd">show spanning-tree vlan 1</span>
VLAN0001
  Spanning tree enabled protocol ieee
  Root ID    Priority    32769
             Address     <span class="cli-highlight-cyan">000c.85ff.bb00</span>
             <span class="cli-highlight-green">This bridge is the root</span></pre>
    </div>

    <!-- القسم الخامس: نصائح اختبار CCNA -->
    <h3 id="sec-exam-tips"><i class="fa-solid fa-shield-halved"></i> 5. نصائح اختبار سيسكو CCNA 200-301 وحقائق بيئات العمل</h3>
    <div class="exam-tips-grid">
        <div class="exam-tip-card">
            <div class="exam-tip-header">
                <i class="fa-solid fa-lightbulb" style="color:#F59E0B;"></i>
                <h4>نصيحة اختبار CCNA في معادلة تكلفة OSPF</h4>
            </div>
            <p>
                المعادلة الافتراضية لحساب التكلفة في سيسكو هي: <code>Cost = 100 Mbps / Bandwidth</code>. لذلك، فإن كلاً من رابط <strong>FastEthernet (100 Mbps)</strong> ورابط <strong>GigabitEthernet (1000 Mbps)</strong> سيأخذ نفس التكلفة (Cost = 1)! ولحل هذه المشكلة في بيئات العمل، يوصى دائماً بتعديل: <code>auto-cost reference-bandwidth 1000</code>.
            </p>
        </div>

        <div class="production-gotcha-card">
            <div class="production-gotcha-header">
                <i class="fa-solid fa-triangle-exclamation" style="color:#E11D48;"></i>
                <h4>خطر الـ Root Bridge غير المقصود في بيئات الإنتاج</h4>
            </div>
            <p>
                إذا أضفت سويتشاً قديماً إلى شبكة الشركة وكانت قيمة الـ Priority الافتراضية فيه هي 32768 ولكن عنوان الـ MAC الخاص به أقل من سويتشات الـ Core الحديثة، سيقوم تلقائياً بتنصيب نفسه <strong>Root Bridge</strong>، مما يوجه حركة بيانات الشبكة بالكامل عبر جهاز قديم وبطيء ويسقط كفاءة الشركة! الحل هو ضبط الـ Core دائماً بـ <code>spanning-tree vlan 1 priority 4096</code>.
            </p>
        </div>
    </div>

</div>
HTML;

$module4_html = <<<'HTML'
<div class="study-guide-content">

    <!-- شريط أهداف التعلم -->
    <div class="callout-box" id="sec-overview">
        <h4><i class="fa-solid fa-graduation-cap"></i> أهداف مسار أمن البنية التحتية وهندسة الدفاع المتعمق (CCNA Security):</h4>
        <p>
            1. <strong>قوائم التحكم بالوصول (ACLs):</strong> إتقان الفروق بين Standard و Extended ومواضع وضعها الهندسية وقاعدة الـ Implicit Deny.<br>
            2. <strong>جدران الحماية (Stateful Firewalls & NGFW):</strong> فهم تتبع الجلسات وجداول الحالة ومقارنتها بفحص الحزم التقليدي.<br>
            3. <strong>تشفير أنفاق IPsec VPN:</strong> فهم بروتوكولي AH و ESP، ومرحلتي التفاوض IKE Phase 1 و Phase 2.<br>
            4. <strong>معمارية انعدام الثقة (Zero Trust):</strong> تطبيق مبدأ <em>"Never Trust, Always Verify"</em> في حماية الشبكات المؤسسية.<br>
            5. <strong>أوامر التحقق الأمني في Cisco IOS:</strong> قراءة إحصاءات مطابقة القواعد <code>show access-lists</code> وحالات التشفير.
        </p>
    </div>

    <!-- القسم الأول: معمارية ACLs -->
    <h3 id="sec-acl-architecture"><i class="fa-solid fa-shield-halved"></i> 1. معمارية قوائم التحكم بالوصول (Access Control Lists - ACLs)</h3>
    <p>
        تُعد قوائم <strong>ACL</strong> خط الدفاع الأول على راوترات ومبدلات سيسكو؛ حيث تتألف من سلسلة مرتبة من شروط السماح <code>permit</code> أو المنع <code>deny</code> لترشيح الحزم:
    </p>

    <table class="academic-table">
        <thead>
            <tr>
                <th>النوع (ACL Type)</th>
                <th>نطاق الأرقام التقليدي</th>
                <th>نطاق الأرقام الموسع</th>
                <th>معايير الترشيح والتدقيق</th>
                <th>الموضع الهندسي الموصى به لسيسكو</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Standard ACL</strong></td>
                <td>1 – 99</td>
                <td>1300 – 1999</td>
                <td>تفحص <strong>عنوان IP المصدر فقط (Source IP)</strong>.</td>
                <td>توضع بالقرب من <strong>الوجهة (Destination)</strong> لتجنب حجب غير مقصود للمصدر عن باقي الشبكات.</td>
            </tr>
            <tr>
                <td><strong>Extended ACL</strong></td>
                <td>100 – 199</td>
                <td>2000 – 2699</td>
                <td>تفحص: IP المصدر والوجهة، نوع البروتوكول (TCP/UDP/ICMP)، وأرقام منافذ التطبيق (Port Numbers).</td>
                <td>توضع بالقرب من <strong>المصدر (Source)</strong> لتوفير سعة القنوات ومنع الحزم غير المرغوبة في أبكر نقطة ممكنة.</td>
            </tr>
        </tbody>
    </table>

    <div class="callout-box" style="border-right-color:#E11D48; margin-top:16px;">
        <strong style="color:#E11D48;"><i class="fa-solid fa-triangle-exclamation"></i> القاعدة الذهبية الحاسمة: الرفض الضمني (Implicit Deny Any Any)</strong>
        <p style="margin:6px 0 0;">
            كل قائمة ACL في سيسكو تنتهي تلقائياً بسطر خفي غير مكتوب: <code>deny ip any any</code>. لذلك، إذا كتبت قائمة ACL لمنع جهاز واحد فقط ولم تضف قاعدة تسمح لباقي الشبكة (مثل <code>permit ip any any</code>)، فسيتم حجب جميع أجهزة الشبكة بالكامل دون استثناء!
        </p>
    </div>

    <!-- القسم الثاني: جدران الحماية -->
    <h3 id="sec-firewall-dynamics"><i class="fa-solid fa-fire-extinguisher"></i> 2. جدران الحماية (Stateful Inspection vs Next-Generation Firewalls)</h3>
    <p>
        تطورت تقنيات جدران الحماية من مجرد مرشحات بسيطة للحزم إلى أنظمة ذكية تتتبع سياق الاتصال في الوقت الفعلي:
    </p>

    <div class="firewall-grid">
        <div class="firewall-card">
            <h4><i class="fa-solid fa-filter"></i> فحص الحزم الثابت (Packet Filtering)</h4>
            <p>يفحص كل حزمة على حدة بمعزل عن الحزم السابقة. لا يعلم ما إذا كانت الحزمة طلباً جديداً أم رداً على طلب موثوق؛ مما يجعله عرضة لهجمات تزييف العناوين (IP Spoofing).</p>
        </div>

        <div class="firewall-card">
            <h4><i class="fa-solid fa-table-list"></i> فحص الحالة (Stateful Inspection)</h4>
            <p>يحتفظ بـ <strong>جدول الحالة (State Table)</strong> للاتصالات النشطة. عندما يرسل مستخدم داخلي طلباً إلى الإنترنت، يسجل الجدار الناري رقم المنفذ والعنوان، ويسمح لحزم الرد بالدخول تلقائياً دون الحاجة لقاعدة صريحة.</p>
        </div>

        <div class="firewall-card">
            <h4><i class="fa-solid fa-shield-virus"></i> جدار حماية الجيل القادم (NGFW)</h4>
            <p>يتفحص حتى الطبقة السابعة (Application Layer 7)، ويتضمن فك تشفير SSL/TLS، وحماية مدمجة ضد الاختراق (IPS/IDS)، والتحكم بالتطبيقات (مثل السماح بـ Facebook مع حجب ألعاب Facebook).</p>
        </div>
    </div>

    <!-- القسم الثالث: أنفاق IPsec VPN -->
    <h3 id="sec-vpn-cryptography"><i class="fa-solid fa-lock"></i> 3. بروتوكولات الأمان وأنفاق IPsec VPN</h3>
    <p>
        توفر أنفاق <strong>IPsec VPN</strong> قناة مشفرة وآمنة لنقل البيانات الحساسة بين فروع المؤسسة عبر شبكة الإنترنت العامة:
    </p>

    <div class="ipsec-comparison-grid">
        <div class="ipsec-card">
            <div class="ipsec-header">
                <span class="badge badge-secondary">IP Protocol 51</span>
                <h4>بروتوكول AH (Authentication Header)</h4>
            </div>
            <p>يوفر <strong>النزاهة (Integrity)</strong> ومصادقة المصدر واكتشاف التلاعب بالبيانات، ولكنه <strong>لا يوفر أي تشفير (No Encryption)</strong>؛ بحيث يمكن قراءة محتوى الحزمة إذا تم التقاطها.</p>
        </div>

        <div class="ipsec-card">
            <div class="ipsec-header">
                <span class="badge badge-primary">IP Protocol 50</span>
                <h4>بروتوكول ESP (Encapsulating Security Payload)</h4>
            </div>
            <p>يوفر <strong>التشفير الكامل لسرية البيانات (Confidentiality عبر AES-256)</strong>، بالإضافة إلى النزاهة والمصادقة. وهو المعيار المعتمد في جميع أنفاق الـ VPN المؤسسية الحديثة.</p>
        </div>
    </div>

    <!-- القسم الرابع: أوامر سيسكو CLI -->
    <h3 id="sec-cisco-cli"><i class="fa-solid fa-terminal"></i> 4. موجه أوامر سيسكو لتكوين والتحقق من الأمان وقوائم ACL</h3>
    <div class="cisco-terminal-box">
        <div class="terminal-bar">
            <div class="terminal-dots">
                <span class="dot-red"></span>
                <span class="dot-yellow"></span>
                <span class="dot-green"></span>
            </div>
            <div class="terminal-title"><i class="fa-solid fa-terminal"></i> Cisco Security Router — Access Lists & IPsec Verification</div>
            <button type="button" class="cli-copy-btn" onclick="copyTerminalText('cli-block-4')">
                <i class="fa-regular fa-copy"></i> نسخ الأوامر
            </button>
        </div>
        <pre class="terminal-content" id="cli-block-4"><span class="cli-prompt">Router(config)#</span> <span class="cli-cmd">ip access-list extended SECURE_DMZ</span>
<span class="cli-prompt">Router(config-ext-nacl)#</span> <span class="cli-cmd">permit tcp 192.168.10.0 0.0.0.255 host 172.16.1.100 eq 443</span>
<span class="cli-prompt">Router(config-ext-nacl)#</span> <span class="cli-cmd">deny ip any host 172.16.1.100</span>
<span class="cli-prompt">Router(config-ext-nacl)#</span> <span class="cli-cmd">permit ip any any</span>
<span class="cli-prompt">Router(config-ext-nacl)#</span> <span class="cli-cmd">exit</span>
<span class="cli-prompt">Router(config)#</span> <span class="cli-cmd">interface GigabitEthernet0/1</span>
<span class="cli-prompt">Router(config-if)#</span> <span class="cli-cmd">ip access-group SECURE_DMZ in</span>

<span class="cli-prompt">Router#</span> <span class="cli-cmd">show access-lists SECURE_DMZ</span>
Extended IP access list SECURE_DMZ
    10 permit tcp 192.168.10.0 0.0.0.255 host 172.16.1.100 eq 443 (<span class="cli-highlight-green">482 matches</span>)
    20 deny ip any host 172.16.1.100 (<span class="cli-highlight-red">14 matches</span>)
    30 permit ip any any (<span class="cli-highlight-cyan">1204 matches</span>)</pre>
    </div>

    <!-- القسم الخامس: نصائح اختبار CCNA وحقائق بيئات العمل -->
    <h3 id="sec-exam-tips"><i class="fa-solid fa-shield-halved"></i> 5. نصائح اختبار سيسكو CCNA 200-301 وحقائق بيئات العمل</h3>
    <div class="exam-tips-grid">
        <div class="exam-tip-card">
            <div class="exam-tip-header">
                <i class="fa-solid fa-lightbulb" style="color:#F59E0B;"></i>
                <h4>نصيحة اختبار CCNA في كتابة الـ Wildcard Mask</h4>
            </div>
            <p>
                في أسئلة Extended ACL، انتبه دائماً إلى كلمة <code>host</code> و <code>any</code>:
                <br>• كتابة <code>host 192.168.1.5</code> تعادل تماماً كتابة <code>192.168.1.5 0.0.0.0</code>.
                <br>• كتابة <code>any</code> تعادل تماماً كتابة <code>0.0.0.0 255.255.255.255</code>.
                <br>تأكد من مراجعة ترتيب القواعد لأن الراوتر ينفذ القواعد بالتسلسل من الأعلى للأسفل، ويتوقف عند أول قاعدة متطابقة!
            </p>
        </div>

        <div class="production-gotcha-card">
            <div class="production-gotcha-header">
                <i class="fa-solid fa-triangle-exclamation" style="color:#E11D48;"></i>
                <h4>ثغرات المنافذ غير المشفرة في بيئات العمل</h4>
            </div>
            <p>
                في تدقيقات الأمان المؤسسية، يُعد استخدام <strong>Telnet (Port 23)</strong> أو <strong>HTTP (Port 80)</strong> لإدارة الأجهزة خطأ أمني فادح؛ لأن كلمات المرور تنتقل كنص واضح (Cleartext) ويمكن اعتراضها بـ Wireshark بسهولة. المعيار الإلزامي هو إغلاق Telnet وتفعيل <strong>SSHv2 (Port 22)</strong> و <strong>HTTPS (Port 443)</strong> حصراً.
            </p>
        </div>
    </div>

</div>
HTML;

$update_stmt = $pdo->prepare("UPDATE study_modules SET content_html = ? WHERE level_id = ?");

$update_stmt->execute([$module1_html, 1]);
echo "Module 1 updated successfully!\n";

$update_stmt->execute([$module2_html, 2]);
echo "Module 2 updated successfully!\n";

$update_stmt->execute([$module3_html, 3]);
echo "Module 3 updated successfully!\n";

$update_stmt->execute([$module4_html, 4]);
echo "Module 4 updated successfully!\n";

echo "All 4 study modules updated with realistic, advanced engineering content!\n";
