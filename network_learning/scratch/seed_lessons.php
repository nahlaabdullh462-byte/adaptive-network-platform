<?php
require "config/db.php";
$pdo->exec("DELETE FROM level_lessons");

$lessons = [

// ======================== المستوى 1 ========================
[
  1, "ما هي الشبكة؟ المفاهيم الأساسية من الصفر", 5,
  "تعلّم ما هي الشبكة الحاسوبية، ولماذا وُجدت، وما هي مكوناتها الأساسية من أجهزة وبروتوكولات وكابلات.",
  '<h2>ما هي الشبكة الحاسوبية؟</h2>
<p>الشبكة الحاسوبية هي مجموعة من الأجهزة المتصلة ببعضها لتتشارك البيانات والموارد. تخيّل أنك في مكتب وكل موظف يطبع من طابعة واحدة — هذا بفضل الشبكة.</p>
<h3>🔹 لماذا نحتاج الشبكات؟</h3>
<ul>
  <li><strong>مشاركة الملفات:</strong> إرسال المستندات بين الأجهزة فوراً</li>
  <li><strong>مشاركة الموارد:</strong> طابعة واحدة، اتصال إنترنت واحد لعشرات الأجهزة</li>
  <li><strong>التواصل:</strong> البريد الإلكتروني، المحادثات الفورية، مكالمات الفيديو</li>
  <li><strong>الأمان المركزي:</strong> إدارة صلاحيات كل جهاز من مكان واحد</li>
</ul>
<h3>🔹 مكونات الشبكة الأساسية</h3>
<table>
  <thead><tr><th>المكوّن</th><th>الوظيفة</th><th>مثال</th></tr></thead>
  <tbody>
    <tr><td><strong>End Device</strong></td><td>الجهاز الطرفي الذي يرسل ويستقبل البيانات</td><td>حاسوب، هاتف، طابعة</td></tr>
    <tr><td><strong>Switch</strong></td><td>يوصّل الأجهزة داخل الشبكة المحلية</td><td>Cisco Catalyst 2960</td></tr>
    <tr><td><strong>Router</strong></td><td>يوجّه البيانات بين الشبكات المختلفة</td><td>Cisco ISR 4000</td></tr>
    <tr><td><strong>Media (وسيط النقل)</strong></td><td>القناة التي تنقل البيانات</td><td>كابل Ethernet، Wi-Fi</td></tr>
  </tbody>
</table>
<h3>🔹 أنواع الشبكات حسب المساحة</h3>
<div class="info-box">
  <p>📍 <strong>LAN (Local Area Network):</strong> شبكة محلية داخل مبنى أو حرم جامعي. السرعة عالية جداً (حتى 10 Gbps).</p>
  <p>🌆 <strong>MAN (Metropolitan Area Network):</strong> تغطي مدينة كاملة. مثال: شبكة مزودي الإنترنت داخل مدينة.</p>
  <p>🌍 <strong>WAN (Wide Area Network):</strong> تربط بين دول وقارات. الإنترنت هو أكبر WAN في العالم.</p>
  <p>🏠 <strong>PAN (Personal Area Network):</strong> شبكة شخصية. مثال: هاتفك والبلوتوث مع سماعاتك.</p>
</div>
<h3>🔹 طوبولوجيا الشبكة (Network Topology)</h3>
<p>الطوبولوجيا هي الشكل الهندسي لترتيب الأجهزة في الشبكة:</p>
<ul>
  <li><strong>Bus:</strong> كل الأجهزة على سلك واحد — قديمة وغير موثوقة</li>
  <li><strong>Ring:</strong> الأجهزة في دائرة — إذا انقطع جهاز انقطعت الشبكة</li>
  <li><strong>Star ⭐ (الأكثر شيوعاً):</strong> كل جهاز متصل بـ Switch مركزي — موثوقة وسهلة الإدارة</li>
  <li><strong>Mesh:</strong> كل جهاز متصل بكل الأجهزة الأخرى — مكلفة لكن فائقة الموثوقية (تُستخدم في ISPs)</li>
</ul>
<div class="warning-box">
  <strong>⚡ نقطة الاختبار:</strong> معظم شبكات الشركات تستخدم Star Topology مع Switch لأنها توفر عزل الأعطال — إذا انقطع كابل جهاز واحد لا تتأثر بقية الشبكة.
</div>
<h3>🔹 خلاصة</h3>
<p>الشبكة = أجهزة + وسائل نقل + بروتوكولات. في الدروس القادمة سنتعمق في كيفية تواصل هذه الأجهزة عبر نموذج OSI الشهير.</p>',
  1
],

[
  1, "نموذج OSI — الشرح الكامل للطبقات السبع", 15,
  "فهم نموذج OSI السبع طبقات بالتفصيل، وظيفة كل طبقة، والبروتوكولات التي تعمل عليها، مع أمثلة عملية.",
  '<h2>نموذج OSI — Open Systems Interconnection</h2>
<p>وضعت منظمة ISO عام 1984 نموذجاً مرجعياً لتوحيد كيفية تواصل الأجهزة المختلفة. هذا النموذج يقسّم عملية الاتصال إلى <strong>7 طبقات</strong>، كل طبقة لها مهمة محددة.</p>
<div class="info-box">
  <strong>قاعدة ذهبية:</strong> البيانات تنزل من الطبقة 7 إلى 1 عند الإرسال، وترتفع من 1 إلى 7 عند الاستقبال.
</div>
<h3>الطبقات السبع من الأعلى للأسفل</h3>
<h4>7️⃣ طبقة التطبيق (Application Layer)</h4>
<p>الطبقة الأقرب للمستخدم. تتعامل مع التطبيقات مباشرة.</p>
<ul>
  <li>البروتوكولات: HTTP/HTTPS، FTP، SMTP، DNS، Telnet، SSH</li>
  <li>مثال: عندما تفتح المتصفح وتكتب عنوان موقع، يستخدم HTTP</li>
</ul>
<h4>6️⃣ طبقة العرض (Presentation Layer)</h4>
<p>تترجم البيانات وتضمن تنسيقها الصحيح. تتولى:</p>
<ul>
  <li>التشفير والفك (Encryption/Decryption) — مثل SSL/TLS</li>
  <li>الضغط (Compression) — مثل ZIP</li>
  <li>تحويل الصياغة (ASCII → EBCDIC)</li>
</ul>
<h4>5️⃣ طبقة الجلسة (Session Layer)</h4>
<p>تُنشئ وتدير وتُنهي جلسات الاتصال بين التطبيقات.</p>
<ul>
  <li>مثال: عند مشاهدة فيديو على يوتيوب، تبقى الجلسة مفتوحة طوال مدة المشاهدة</li>
  <li>بروتوكولات: NetBIOS، RPC</li>
</ul>
<h4>4️⃣ طبقة النقل (Transport Layer) ⭐ الأهم</h4>
<p>المسؤولة عن نقل البيانات بشكل موثوق من طرف إلى طرف.</p>
<table>
  <thead><tr><th>TCP</th><th>UDP</th></tr></thead>
  <tbody>
    <tr><td>موثوق (Reliable)</td><td>غير موثوق (Unreliable)</td></tr>
    <tr><td>يضمن وصول كل الحزم</td><td>لا يتحقق من الوصول</td></tr>
    <tr><td>أبطأ (بسبب التأكيد)</td><td>أسرع</td></tr>
    <tr><td>HTTP, FTP, SSH, SMTP</td><td>DNS, VoIP, Video Streaming, Gaming</td></tr>
  </tbody>
</table>
<p><strong>Port Numbers:</strong> HTTP=80, HTTPS=443, FTP=21, SSH=22, DNS=53, SMTP=25</p>
<h4>3️⃣ طبقة الشبكة (Network Layer)</h4>
<p>مسؤولة عن التوجيه (Routing) وعنونة الأجهزة منطقياً (IP Addressing).</p>
<ul>
  <li>الوحدة: Packet (حزمة)</li>
  <li>البروتوكولات: IP (IPv4/IPv6)، ICMP (ping)، ARP، OSPF، RIP</li>
  <li>الجهاز: Router</li>
</ul>
<h4>2️⃣ طبقة وصلة البيانات (Data Link Layer)</h4>
<p>تضمن نقل البيانات بين جهازين متجاورين على نفس الشبكة.</p>
<ul>
  <li>الوحدة: Frame (إطار)</li>
  <li>تستخدم MAC Address للتعرف على الأجهزة</li>
  <li>البروتوكولات: Ethernet, Wi-Fi (802.11), PPP</li>
  <li>الجهاز: Switch, Bridge, NIC</li>
</ul>
<h4>1️⃣ طبقة الفيزياء (Physical Layer)</h4>
<p>تتعامل مع الإشارات الكهربائية والضوئية والراديوية.</p>
<ul>
  <li>الوحدة: Bit (0s and 1s)</li>
  <li>أنواع الكابلات: UTP، STP، Fiber Optic، Coaxial</li>
  <li>الأجهزة: Hub، Repeater، Modem</li>
</ul>
<div class="warning-box">
  <strong>⚡ طريقة الحفظ السريعة:</strong><br>
  <strong>All People Seem To Need Data Processing</strong><br>
  A=Application, P=Presentation, S=Session, T=Transport, N=Network, D=Data Link, P=Physical
</div>
<h3>مثال عملي: ماذا يحدث عندما تفتح Google؟</h3>
<ol>
  <li><strong>Layer 7:</strong> المتصفح يستخدم HTTP GET لطلب الصفحة</li>
  <li><strong>Layer 6:</strong> TLS يشفر البيانات (HTTPS)</li>
  <li><strong>Layer 5:</strong> يُنشئ جلسة TCP مع خادم Google</li>
  <li><strong>Layer 4:</strong> TCP يقسّم البيانات إلى Segments ويضيف Port 443</li>
  <li><strong>Layer 3:</strong> IP يضيف عنوان Google كـ Destination IP</li>
  <li><strong>Layer 2:</strong> Ethernet يضيف MAC Address للـ Router القريب</li>
  <li><strong>Layer 1:</strong> الإشارة الكهربائية تسافر عبر الكابل</li>
</ol>',
  1
],

[
  1, "بروتوكول TCP/IP — كيف يعمل الإنترنت فعلاً؟", 12,
  "فهم مجموعة بروتوكولات TCP/IP التي تشغّل الإنترنت، مقارنتها بنموذج OSI، وفهم عمل كل طبقة.",
  '<h2>مجموعة بروتوكولات TCP/IP</h2>
<p>TCP/IP هو النموذج العملي الذي يُشغّل الإنترنت فعلاً. يتكون من <strong>4 طبقات</strong> فقط (مقارنة بـ 7 في OSI).</p>
<h3>المقارنة بين OSI و TCP/IP</h3>
<table>
  <thead><tr><th>OSI (7 طبقات)</th><th>TCP/IP (4 طبقات)</th></tr></thead>
  <tbody>
    <tr><td>Application (7)</td><td rowspan="3">Application</td></tr>
    <tr><td>Presentation (6)</td></tr>
    <tr><td>Session (5)</td></tr>
    <tr><td>Transport (4)</td><td>Transport</td></tr>
    <tr><td>Network (3)</td><td>Internet</td></tr>
    <tr><td>Data Link (2)</td><td rowspan="2">Network Access</td></tr>
    <tr><td>Physical (1)</td></tr>
  </tbody>
</table>
<h3>🔹 طبقة التطبيق في TCP/IP</h3>
<p>تضم كل بروتوكولات OSI العليا (7, 6, 5). البروتوكولات الأساسية:</p>
<ul>
  <li><strong>HTTP/HTTPS:</strong> تصفح الويب (Port 80 / 443)</li>
  <li><strong>DNS:</strong> تحويل الأسماء لعناوين IP (Port 53)</li>
  <li><strong>DHCP:</strong> توزيع عناوين IP تلقائياً (Port 67/68)</li>
  <li><strong>FTP:</strong> نقل الملفات (Port 21)</li>
  <li><strong>SSH:</strong> الإدارة الآمنة عن بُعد (Port 22)</li>
  <li><strong>SMTP/POP3/IMAP:</strong> البريد الإلكتروني</li>
  <li><strong>SNMP:</strong> إدارة الشبكات (Port 161)</li>
  <li><strong>NTP:</strong> مزامنة الوقت (Port 123)</li>
</ul>
<h3>🔹 طبقة النقل: TCP مقابل UDP</h3>
<div class="info-box">
  <strong>TCP (Transmission Control Protocol):</strong>
  <ul>
    <li>Three-Way Handshake: SYN → SYN-ACK → ACK</li>
    <li>يضمن وصول الحزم بالترتيب الصحيح</li>
    <li>يُعيد إرسال الحزم الضائعة</li>
    <li>مناسب لـ: تحميل الملفات، المواقع، البريد الإلكتروني</li>
  </ul>
</div>
<div class="warning-box">
  <strong>UDP (User Datagram Protocol):</strong>
  <ul>
    <li>لا يوجد Handshake — يرسل مباشرة</li>
    <li>لا يتأكد من وصول الحزم</li>
    <li>سريع جداً وأقل استهلاكاً للموارد</li>
    <li>مناسب لـ: بث الفيديو، المكالمات الصوتية، الألعاب</li>
  </ul>
</div>
<h3>🔹 TCP Three-Way Handshake</h3>
<ol>
  <li><strong>SYN:</strong> العميل يرسل "أريد التحدث"</li>
  <li><strong>SYN-ACK:</strong> الخادم يرد "حسناً، تفضل"</li>
  <li><strong>ACK:</strong> العميل يقول "شكراً، نبدأ"</li>
</ol>
<h3>🔹 طبقة الإنترنت: IP</h3>
<p>مسؤولة عن العنونة المنطقية والتوجيه. IPv4 يستخدم عناوين 32-bit (مثل 192.168.1.1) بينما IPv6 يستخدم 128-bit.</p>
<p><strong>ICMP:</strong> بروتوكول الرسائل التحكمية — يُستخدم بواسطة أمر <code>ping</code> لاختبار الاتصال.</p>',
  1
],

// ======================== المستوى 2 ========================
[
  2, "عنونة IPv4 — الشرح الكامل مع الأمثلة", 18,
  "تعلّم عنونة IPv4 من الصفر: البنية، الفئات، القناع، CIDR، وكيف تقرأ وتكتب العناوين بثقة.",
  '<h2>عنونة IPv4 — الشرح الكامل</h2>
<p>عنوان IPv4 هو رقم مكوّن من <strong>32 bit</strong> مقسّمة على 4 أقسام (Octets) كل قسم 8 bits.</p>
<h3>🔹 بنية عنوان IPv4</h3>
<div class="info-box">
  <code>192.168.1.100</code><br>
  كل رقم بين النقاط يتراوح من <strong>0 إلى 255</strong> (8 bits).<br>
  إجمالي العناوين الممكنة: 2³² = <strong>4,294,967,296 عنوان</strong>
</div>
<h3>🔹 أجزاء العنوان</h3>
<p>كل عنوان IP مقسّم إلى جزأين:</p>
<ul>
  <li><strong>Network ID:</strong> يحدد الشبكة</li>
  <li><strong>Host ID:</strong> يحدد الجهاز داخل الشبكة</li>
</ul>
<p>الـ Subnet Mask يحدد الحد بين الجزأين.</p>
<h3>🔹 فئات عناوين IPv4</h3>
<table>
  <thead><tr><th>الفئة</th><th>المدى</th><th>Default Mask</th><th>الاستخدام</th></tr></thead>
  <tbody>
    <tr><td><strong>Class A</strong></td><td>1.0.0.0 — 126.255.255.255</td><td>/8 (255.0.0.0)</td><td>شبكات ضخمة جداً</td></tr>
    <tr><td><strong>Class B</strong></td><td>128.0.0.0 — 191.255.255.255</td><td>/16 (255.255.0.0)</td><td>شبكات متوسطة</td></tr>
    <tr><td><strong>Class C</strong></td><td>192.0.0.0 — 223.255.255.255</td><td>/24 (255.255.255.0)</td><td>شبكات صغيرة</td></tr>
    <tr><td><strong>Class D</strong></td><td>224.0.0.0 — 239.255.255.255</td><td>—</td><td>Multicast</td></tr>
    <tr><td><strong>Class E</strong></td><td>240.0.0.0 — 255.255.255.255</td><td>—</td><td>محجوزة للبحث</td></tr>
  </tbody>
</table>
<h3>🔹 العناوين الخاصة (Private Addresses)</h3>
<div class="info-box">
  هذه العناوين مخصصة للشبكات الداخلية فقط ولا يمكن توجيهها على الإنترنت:
  <ul>
    <li>10.0.0.0 — 10.255.255.255 (Class A)</li>
    <li>172.16.0.0 — 172.31.255.255 (Class B)</li>
    <li>192.168.0.0 — 192.168.255.255 (Class C)</li>
  </ul>
</div>
<h3>🔹 عناوين خاصة أخرى مهمة</h3>
<ul>
  <li><strong>127.0.0.1 (Loopback):</strong> يُشير للجهاز نفسه — ping 127.0.0.1 لاختبار TCP/IP Stack</li>
  <li><strong>169.254.x.x (APIPA):</strong> يُعيّن تلقائياً إذا فشل DHCP</li>
  <li><strong>0.0.0.0:</strong> Default Route</li>
  <li><strong>255.255.255.255:</strong> Limited Broadcast</li>
</ul>
<h3>🔹 CIDR — Classless Inter-Domain Routing</h3>
<p>بدلاً من كتابة 255.255.255.0 نكتب /24 لأن عدد الـ 1s في القناع = 24.</p>
<table>
  <thead><tr><th>CIDR</th><th>Subnet Mask</th><th>عدد العناوين</th><th>عدد Hosts</th></tr></thead>
  <tbody>
    <tr><td>/24</td><td>255.255.255.0</td><td>256</td><td>254</td></tr>
    <tr><td>/25</td><td>255.255.255.128</td><td>128</td><td>126</td></tr>
    <tr><td>/26</td><td>255.255.255.192</td><td>64</td><td>62</td></tr>
    <tr><td>/27</td><td>255.255.255.224</td><td>32</td><td>30</td></tr>
    <tr><td>/28</td><td>255.255.255.240</td><td>16</td><td>14</td></tr>
    <tr><td>/30</td><td>255.255.255.252</td><td>4</td><td>2</td></tr>
  </tbody>
</table>
<div class="warning-box">
  <strong>⚡ القاعدة:</strong> عدد Hosts = 2^(32 - prefix) - 2<br>
  نطرح 2 لأن أول عنوان = Network Address وآخر عنوان = Broadcast Address
</div>',
  1
],

[
  2, "Subnetting خطوة بخطوة — من السؤال للإجابة في دقائق", 20,
  "إتقان حسابات Subnetting بطريقة منهجية واضحة. تعلّم كيف تُقسّم أي شبكة وتحسب Network ID, Broadcast, Range.",
  '<h2>Subnetting — تقسيم الشبكات الفرعية</h2>
<p>Subnetting هو تقسيم شبكة كبيرة إلى شبكات أصغر. نستخدمه لـ:</p>
<ul>
  <li>تقليل الازدحام في الشبكة</li>
  <li>تحسين الأمان (عزل الأقسام)</li>
  <li>الاستخدام الأمثل للعناوين</li>
</ul>
<h3>🔹 الخطوة 1: فهم القناع (Subnet Mask)</h3>
<div class="info-box">
  القناع يُحدد أي جزء من العنوان يمثل الشبكة وأيه يمثل الأجهزة.<br>
  في /24: الـ 24 bit الأولى = Network، الـ 8 الأخيرة = Hosts<br>
  255.255.255.0 بالـ Binary: 11111111.11111111.11111111.00000000
</div>
<h3>🔹 الخطوة 2: حساب عدد الشبكات والأجهزة</h3>
<p>إذا أخذنا n bit من Host ID لتصبح Subnet bits:</p>
<ul>
  <li>عدد الشبكات = 2^n</li>
  <li>عدد Hosts لكل شبكة = 2^(8-n) - 2</li>
</ul>
<h3>🔹 مثال عملي كامل</h3>
<div class="info-box">
  <strong>السؤال:</strong> قسّم الشبكة 192.168.1.0/24 إلى 4 شبكات فرعية متساوية.<br><br>
  <strong>الحل:</strong><br>
  نحتاج 4 شبكات = 2² ← نأخذ 2 bit من Host ID<br>
  القناع الجديد: /24 + 2 = /26<br>
  255.255.255.192<br><br>
  عدد Hosts لكل شبكة: 2^(8-2) - 2 = 64 - 2 = 62 host
</div>
<table>
  <thead><tr><th>الشبكة</th><th>Network ID</th><th>أول Host</th><th>آخر Host</th><th>Broadcast</th></tr></thead>
  <tbody>
    <tr><td>Subnet 1</td><td>192.168.1.0</td><td>192.168.1.1</td><td>192.168.1.62</td><td>192.168.1.63</td></tr>
    <tr><td>Subnet 2</td><td>192.168.1.64</td><td>192.168.1.65</td><td>192.168.1.126</td><td>192.168.1.127</td></tr>
    <tr><td>Subnet 3</td><td>192.168.1.128</td><td>192.168.1.129</td><td>192.168.1.190</td><td>192.168.1.191</td></tr>
    <tr><td>Subnet 4</td><td>192.168.1.192</td><td>192.168.1.193</td><td>192.168.1.254</td><td>192.168.1.255</td></tr>
  </tbody>
</table>
<h3>🔹 الطريقة السريعة: Magic Number</h3>
<p>Magic Number = 256 - قيمة القناع في الـ Octet المهتم به</p>
<div class="warning-box">
  <strong>مثال:</strong> /26 → القناع = 255.255.255.192<br>
  Magic Number = 256 - 192 = <strong>64</strong><br>
  الشبكات تبدأ عند: 0، 64، 128، 192<br>
  كل شبكة تحتوي على 64 عنوان (62 للأجهزة)
</div>',
  1
],

// ======================== المستوى 3 ========================
[
  3, "بروتوكولات التوجيه: RIP, OSPF, EIGRP — المقارنة الشاملة", 20,
  "فهم بروتوكولات التوجيه الديناميكية، كيف تعمل، متى تستخدم كل منها، وكيف تُعدّها على Cisco IOS.",
  '<h2>بروتوكولات التوجيه الديناميكية</h2>
<p>بروتوكولات التوجيه تُمكّن الـ Routers من اكتشاف الشبكات تلقائياً ومشاركة معلومات التوجيه فيما بينها.</p>
<h3>🔹 تصنيف بروتوكولات التوجيه</h3>
<table>
  <thead><tr><th>المعيار</th><th>Distance Vector</th><th>Link State</th><th>Hybrid</th></tr></thead>
  <tbody>
    <tr><td><strong>مثال</strong></td><td>RIP, IGRP</td><td>OSPF, IS-IS</td><td>EIGRP</td></tr>
    <tr><td><strong>يشارك</strong></td><td>Routing Table كاملة</td><td>معلومات الروابط فقط</td><td>الاثنان</td></tr>
    <tr><td><strong>السرعة</strong></td><td>بطيء</td><td>سريع جداً</td><td>سريع جداً</td></tr>
    <tr><td><strong>الذاكرة</strong></td><td>قليلة</td><td>عالية</td><td>متوسطة</td></tr>
  </tbody>
</table>
<h3>🔹 RIP — Routing Information Protocol</h3>
<ul>
  <li><strong>Metric:</strong> Hop Count (عدد الـ Routers)</li>
  <li><strong>Maximum Hops:</strong> 15 (16 = unreachable)</li>
  <li><strong>Update Timer:</strong> كل 30 ثانية يرسل الـ Routing Table كاملة</li>
  <li><strong>RIPv1:</strong> Classful (لا يرسل Subnet Mask) — قديم</li>
  <li><strong>RIPv2:</strong> Classless (يرسل Subnet Mask) — أفضل</li>
</ul>
<div class="info-box">
  <strong>إعداد RIPv2 على Cisco:</strong><br>
  <code>Router(config)# router rip<br>
  Router(config-router)# version 2<br>
  Router(config-router)# network 192.168.1.0<br>
  Router(config-router)# no auto-summary</code>
</div>
<h3>🔹 OSPF — Open Shortest Path First</h3>
<ul>
  <li><strong>Algorithm:</strong> Dijkstra (SPF Algorithm)</li>
  <li><strong>Metric:</strong> Cost = 100Mbps / Interface Bandwidth</li>
  <li><strong>Convergence:</strong> سريع جداً</li>
  <li><strong>Areas:</strong> يُقسّم الشبكة إلى Areas لتقليل الضغط</li>
  <li><strong>Area 0 = Backbone Area</strong> (يجب أن تمر به كل الـ Areas)</li>
</ul>
<div class="info-box">
  <strong>إعداد OSPF على Cisco:</strong><br>
  <code>Router(config)# router ospf 1<br>
  Router(config-router)# network 192.168.1.0 0.0.0.255 area 0<br>
  Router(config-router)# network 10.0.0.0 0.255.255.255 area 0</code>
</div>
<h3>🔹 EIGRP — Enhanced Interior Gateway Routing Protocol</h3>
<ul>
  <li>بروتوكول Cisco الخاص (لكن أصبح مفتوحاً جزئياً)</li>
  <li><strong>Metric:</strong> Bandwidth + Delay (+ Load + Reliability اختياري)</li>
  <li><strong>DUAL Algorithm:</strong> يحتفظ بمسار احتياطي (Feasible Successor)</li>
  <li>لا يرسل الـ Routing Table كاملة — فقط التغييرات</li>
</ul>
<div class="warning-box">
  <strong>⚡ للاختبار:</strong> AD (Administrative Distance) = مقياس الموثوقية<br>
  Connected=0, Static=1, EIGRP=90, OSPF=110, RIP=120<br>
  القيمة الأصغر = الأكثر موثوقية = يُفضَّل
</div>',
  1
],

// ======================== المستوى 4 ========================
[
  4, "أمن الشبكات — المفاهيم الأساسية والتهديدات والحلول", 18,
  "فهم أنواع تهديدات الشبكات، CIA Triad، أنواع الهجمات الشائعة، وكيف تحمي شبكتك.",
  '<h2>أمن الشبكات — Network Security</h2>
<p>أمن الشبكات هو مجموعة من الإجراءات والتقنيات التي تحمي الشبكة من الوصول غير المصرح به، وسرقة البيانات، والهجمات الإلكترونية.</p>
<h3>🔹 CIA Triad — ثالوث الأمن</h3>
<div class="info-box">
  <strong>C — Confidentiality (السرية):</strong> البيانات تصل فقط للمصرّح لهم<br>
  <strong>I — Integrity (النزاهة):</strong> البيانات لم تُعدَّل أثناء النقل<br>
  <strong>A — Availability (التوافر):</strong> الشبكة متاحة للمستخدمين المصرّح لهم دائماً
</div>
<h3>🔹 أنواع التهديدات الشائعة</h3>
<table>
  <thead><tr><th>الهجوم</th><th>الوصف</th><th>الحماية</th></tr></thead>
  <tbody>
    <tr><td><strong>DoS/DDoS</strong></td><td>إغراق الخادم بطلبات وهمية لإيقافه</td><td>Firewall, IPS, Rate Limiting</td></tr>
    <tr><td><strong>Man-in-the-Middle</strong></td><td>المهاجم يجلس بين الطرفين ويعترض البيانات</td><td>TLS/SSL, VPN</td></tr>
    <tr><td><strong>ARP Spoofing</strong></td><td>إرسال ARP Replies مزيفة لتحويل الترافيك</td><td>Dynamic ARP Inspection</td></tr>
    <tr><td><strong>DNS Poisoning</strong></td><td>تعديل سجلات DNS لتحويل المستخدمين لمواقع مزيفة</td><td>DNSSEC</td></tr>
    <tr><td><strong>Phishing</strong></td><td>خداع المستخدم للكشف عن بيانات سرية</td><td>User Awareness, Email Filtering</td></tr>
    <tr><td><strong>Ransomware</strong></td><td>تشفير الملفات وطلب فدية</td><td>Backup, EDR, Email Security</td></tr>
  </tbody>
</table>
<h3>🔹 Access Control Lists (ACLs)</h3>
<p>ACL هي قائمة قواعد تُطبَّق على واجهة Router لتصفية الترافيك.</p>
<div class="info-box">
  <strong>Standard ACL (1-99, 1300-1999):</strong> تُصفّي بناءً على Source IP فقط.<br>
  <code>access-list 10 deny 192.168.2.0 0.0.0.255<br>
  access-list 10 permit any</code><br><br>
  <strong>Extended ACL (100-199, 2000-2699):</strong> تُصفّي بناءً على Source+Destination IP+Port+Protocol.<br>
  <code>access-list 100 deny tcp 192.168.1.0 0.0.0.255 any eq 80<br>
  access-list 100 permit ip any any</code>
</div>
<div class="warning-box">
  <strong>⚡ قاعدة مهمة:</strong> في نهاية كل ACL يوجد <strong>implicit deny any</strong> غير مرئي — إذا لم تُصرّح بترافيك ما سيُرفض تلقائياً!<br>
  <strong>Standard ACL:</strong> ضعها قرب الـ Destination<br>
  <strong>Extended ACL:</strong> ضعها قرب الـ Source
</div>
<h3>🔹 AAA — Authentication, Authorization, Accounting</h3>
<ul>
  <li><strong>Authentication:</strong> التحقق من هوية المستخدم (من أنت؟)</li>
  <li><strong>Authorization:</strong> تحديد ما يُسمح له بفعله (ماذا تستطيع؟)</li>
  <li><strong>Accounting:</strong> تسجيل كل ما فعله (ماذا فعلت؟)</li>
</ul>
<p>بروتوكولات AAA: <strong>RADIUS</strong> (UDP, يشفّر كلمة المرور فقط) و <strong>TACACS+</strong> (TCP, يشفّر كل الجلسة — أكثر أماناً).</p>',
  1
],

[
  4, "VPN والتشفير — حماية البيانات أثناء النقل", 15,
  "فهم كيفية عمل VPN، أنواعه، بروتوكولات التشفير المستخدمة، وكيف يؤمّن الاتصال عبر الإنترنت.",
  '<h2>VPN — Virtual Private Network</h2>
<p>VPN ينشئ نفقاً مشفراً (Encrypted Tunnel) عبر شبكة عامة (الإنترنت) ليجعل الاتصال آمناً كأنك على شبكة خاصة.</p>
<h3>🔹 لماذا نستخدم VPN؟</h3>
<ul>
  <li>حماية البيانات على الشبكات العامة (Wi-Fi المقاهي)</li>
  <li>توصيل المكاتب الفرعية بالمقر الرئيسي بأمان</li>
  <li>السماح للموظفين بالوصول للشبكة الداخلية من أي مكان</li>
  <li>تجاوز القيود الجغرافية</li>
</ul>
<h3>🔹 أنواع VPN</h3>
<table>
  <thead><tr><th>النوع</th><th>الاستخدام</th><th>المثال</th></tr></thead>
  <tbody>
    <tr><td><strong>Site-to-Site VPN</strong></td><td>ربط فرعين دائماً</td><td>مكتب القاهرة ↔ مكتب الرياض</td></tr>
    <tr><td><strong>Remote Access VPN</strong></td><td>موظف يتصل من المنزل</td><td>Cisco AnyConnect, OpenVPN</td></tr>
    <tr><td><strong>SSL/TLS VPN</strong></td><td>عبر المتصفح فقط</td><td>Web-based portal</td></tr>
  </tbody>
</table>
<h3>🔹 بروتوكولات VPN</h3>
<div class="info-box">
  <strong>IPsec (Internet Protocol Security):</strong><br>
  يعمل في Layer 3 ويوفر: Authentication + Encryption + Integrity<br>
  وضعان: <strong>Transport Mode</strong> (يشفّر الـ Payload فقط) و <strong>Tunnel Mode</strong> (يشفّر كامل الـ Packet — الأكثر شيوعاً)
</div>
<div class="info-box">
  <strong>مراحل IPsec:</strong><br>
  1. IKE Phase 1: التفاوض على خوارزميات التشفير وإنشاء قناة آمنة<br>
  2. IKE Phase 2: التفاوض على معاملات IPsec وإنشاء الـ Tunnel الفعلي
</div>
<h3>🔹 مفاهيم التشفير الأساسية</h3>
<ul>
  <li><strong>Symmetric Encryption:</strong> نفس المفتاح للتشفير والفك (AES, 3DES) — سريع</li>
  <li><strong>Asymmetric Encryption:</strong> مفتاح عام للتشفير + مفتاح خاص للفك (RSA, ECDH) — آمن لتبادل المفاتيح</li>
  <li><strong>Hashing:</strong> دالة رياضية أحادية الاتجاه للتحقق من النزاهة (MD5, SHA-256)</li>
</ul>
<div class="warning-box">
  <strong>⚡ للاختبار:</strong><br>
  AES-256 = أقوى تشفير متماثل موصى به<br>
  SHA-256 = أشهر خوارزمية Hashing<br>
  MD5 = ضعيف ومنتهي (لا تستخدمه في الأنظمة الحديثة)
</div>',
  1
],

];

$stmt = $pdo->prepare("INSERT INTO level_lessons (level_id,title,is_featured,summary,content,sort_order) VALUES (?,?,?,?,?,?)");
$count = 0;
foreach ($lessons as $l) {
    [$lid, $title, $sort, $summary, $content, $featured] = $l;
    $stmt->execute([$lid, $title, $featured, $summary, $content, $sort]);
    $count++;
}
echo "Inserted {$count} text lessons!\n";

// ملخص
$summary = $pdo->query("SELECT l.level_name, COUNT(ll.id) as cnt FROM levels l LEFT JOIN level_lessons ll ON ll.level_id=l.id GROUP BY l.id, l.level_name ORDER BY l.level_order")->fetchAll();
foreach ($summary as $s) echo "  -> {$s['level_name']}: {$s['cnt']} lessons\n";
