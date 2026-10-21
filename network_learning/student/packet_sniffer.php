<?php
session_start();
$pageTitle = "محلل ومفتش الحزم وبروتوكولات الشبكة (Packet Sniffer Lab)";

require_once '../config/db.php';
require_once '../config/security.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'طالب';

require_once '../includes/header.php';
?>

<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
    <div>
        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(79, 70, 229, 0.08); border: 1px solid rgba(199, 210, 254, 0.8); padding: 5px 14px; border-radius: var(--radius-full); font-size: 0.82rem; font-weight: 700; color: var(--brand-primary); margin-bottom: 8px;">
            <i class="fa-solid fa-microchip"></i>
            <span>مختبر الفحص العميق للحزم (Deep Packet Inspection & Protocol Dissection)</span>
        </div>
        <h1 style="font-size: 1.85rem; color: var(--text-heading); margin-bottom: 6px;">
            محلل الحزم التفاعلي المتقدم (Wireshark Protocol Inspector)
        </h1>
        <p style="color: var(--text-muted); font-size: 0.94rem; margin-bottom: 0;">
            التقط وافحص حزم TCP/IP و Ethernet في الوقت الفعلي مع تفكيك ترويسات البروتوكولات وعرض مصفوفة الـ Hex Dump.
        </p>
    </div>

    <div style="display: flex; gap: 8px; align-items: center;">
        <span class="badge badge-primary"><i class="fa-solid fa-graduation-cap"></i> متوافق مع معايير CCNA</span>
        <button type="button" onclick="clearPackets()" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-trash-can"></i> مسح السجل
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- لوحة التحكم وتوليد الترافيك والبروتوكولات (Traffic Generator Bar)             -->
<!-- ========================================================================= -->
<div class="academic-card" style="padding: 20px 24px; margin-bottom: 22px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 14px;">
        <div style="font-weight: 700; color: var(--text-heading); font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-paper-plane" style="color: var(--brand-primary);"></i>
            <span>محاكي إرسال الترافيك الأكاديمي (Traffic Generators):</span>
        </div>
        <div style="font-size: 0.84rem; color: var(--text-muted);">
            اختر سيناريو لتوليد الحزم الحقيقية وفحصها فوراً:
        </div>
    </div>

    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" onclick="generateScenario('http')" class="btn btn-secondary btn-sm" style="border-radius: var(--radius-sm); font-size: 0.85rem;">
            <i class="fa-solid fa-globe" style="color: #3B82F6;"></i> طلب ويب HTTP GET (Port 80)
        </button>
        <button type="button" onclick="generateScenario('dns')" class="btn btn-secondary btn-sm" style="border-radius: var(--radius-sm); font-size: 0.85rem;">
            <i class="fa-solid fa-magnifying-glass" style="color: #0D9488;"></i> استعلام DNS Query (Port 53)
        </button>
        <button type="button" onclick="generateScenario('tcp')" class="btn btn-secondary btn-sm" style="border-radius: var(--radius-sm); font-size: 0.85rem;">
            <i class="fa-solid fa-handshake" style="color: #8B5CF6;"></i> مصافحة TCP 3-Way Handshake
        </button>
        <button type="button" onclick="generateScenario('icmp')" class="btn btn-secondary btn-sm" style="border-radius: var(--radius-sm); font-size: 0.85rem;">
            <i class="fa-solid fa-bolt" style="color: #F59E0B;"></i> اختبار اتصال ICMP Echo Ping
        </button>
        <button type="button" onclick="generateScenario('arp')" class="btn btn-secondary btn-sm" style="border-radius: var(--radius-sm); font-size: 0.85rem;">
            <i class="fa-solid fa-broadcast-tower" style="color: #10B981;"></i> بث تحويل العناوين ARP Request
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- شريط التصفية والفلترة الحية (Live Display Filter Bar)                      -->
<!-- ========================================================================= -->
<div style="margin-bottom: 16px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 260px; position: relative;">
        <input type="text" id="filter-input" oninput="applyFilter()" class="form-control" placeholder="فلتر العرض السريع (مثال: tcp, http, dns, icmp, 192.168.1.1)" style="padding-right: 36px; font-family: var(--font-mono); font-size: 0.88rem; direction: ltr; text-align: left;">
        <i class="fa-solid fa-filter" style="position: absolute; right: 12px; top: 13px; color: var(--text-muted); font-size: 0.88rem;"></i>
    </div>
    <div style="display: flex; gap: 6px;">
        <button type="button" onclick="setFilterChip('')" class="btn btn-secondary btn-sm filter-chip active" id="chip-all">الكل</button>
        <button type="button" onclick="setFilterChip('HTTP')" class="btn btn-secondary btn-sm filter-chip" id="chip-http">HTTP</button>
        <button type="button" onclick="setFilterChip('DNS')" class="btn btn-secondary btn-sm filter-chip" id="chip-dns">DNS</button>
        <button type="button" onclick="setFilterChip('TCP')" class="btn btn-secondary btn-sm filter-chip" id="chip-tcp">TCP</button>
        <button type="button" onclick="setFilterChip('ICMP')" class="btn btn-secondary btn-sm filter-chip" id="chip-icmp">ICMP</button>
        <button type="button" onclick="setFilterChip('ARP')" class="btn btn-secondary btn-sm filter-chip" id="chip-arp">ARP</button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- جدول الحزم الملتقطة (Packet Capture Stream Table)                         -->
<!-- ========================================================================= -->
<div class="academic-card" style="padding: 0; overflow: hidden; margin-bottom: 24px;">
    <div style="max-height: 270px; overflow-y: auto;">
        <table class="academic-table" style="margin: 0; font-family: var(--font-mono); font-size: 0.85rem;" id="packet-table">
            <thead>
                <tr style="position: sticky; top: 0; z-index: 5; background: #F8FAFD;">
                    <th style="width: 55px; text-align: center;">#</th>
                    <th style="width: 90px; text-align: center;">الوقت (ث)</th>
                    <th style="width: 140px; text-align: left;">المصدر (Source)</th>
                    <th style="width: 140px; text-align: left;">الوجهة (Destination)</th>
                    <th style="width: 90px; text-align: center;">البروتوكول</th>
                    <th style="width: 80px; text-align: center;">الحجم (B)</th>
                    <th style="text-align: left;">تفاصيل الحزمة (Info)</th>
                </tr>
            </thead>
            <tbody id="packet-tbody">
                <!-- الصفوف تُنشأ تلقائياً بواسطة JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================================================= -->
<!-- قسم التفكيك العميق وعرض الـ Hex (Packet Details & Hex Viewer Split)        -->
<!-- ========================================================================= -->
<div class="grid-2" style="margin-bottom: 24px;">
    
    <!-- لوحة شجرة ترويسات الحزمة (Packet Dissection Tree) -->
    <div class="academic-card" style="padding: 22px;">
        <div style="font-weight: 700; color: var(--text-heading); margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-layer-group" style="color: var(--brand-primary);"></i>
                <span>شجرة تفكيك الترويسات (Dissected Headers)</span>
            </div>
            <span id="selected-packet-badge" class="badge badge-primary">حزمة #1</span>
        </div>

        <div id="dissection-tree" style="font-family: var(--font-mono); font-size: 0.86rem; line-height: 1.6; direction: ltr; text-align: left; max-height: 380px; overflow-y: auto;">
            <!-- الشجرة تُملأ هنا -->
        </div>
    </div>

    <!-- لوحة فحص البايتات بالستة عشري (Hex & ASCII Dump Viewer) -->
    <div class="academic-card" style="padding: 22px;">
        <div style="font-weight: 700; color: var(--text-heading); margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-binary" style="color: #0D9488;"></i>
                <span>مصفوفة البايتات الخام (Raw Hex & ASCII Dump)</span>
            </div>
            <span style="font-size: 0.78rem; color: var(--text-muted); font-family: var(--font-mono);">Offset: 0000 - 0040</span>
        </div>

        <div id="hex-dump-view" style="background: #0F172A; color: #38BDF8; font-family: var(--font-mono); font-size: 0.82rem; padding: 16px; border-radius: var(--radius-sm); line-height: 1.6; direction: ltr; text-align: left; height: 380px; overflow-y: auto; box-shadow: inset 0 2px 8px rgba(0,0,0,0.4);">
            <!-- الـ Hex Dump هنا -->
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- بطاقة التفسير الأكاديمي لمحتويات الحزمة (CCNA Educational Field Analysis)   -->
<!-- ========================================================================= -->
<div class="academic-card" style="padding: 24px; border-top: 3px solid var(--brand-primary);">
    <div style="font-weight: 700; color: var(--text-heading); font-size: 1.05rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-graduation-cap" style="color: var(--brand-primary);"></i>
        <span>التفسير الهندسي لترويسات الحزمة المختارة ومعايير Cisco CCNA:</span>
    </div>
    
    <div id="field-explanation" style="font-size: 0.92rem; color: var(--text-body); line-height: 1.7;">
        اختر أي حزمة من الجدول أعلاه لعرض تحليل تفصيلي لترويسات الطبقات الفيزيائية وطبقة ربط البيانات، والشبكة، والنقل والتطبيقات.
    </div>
</div>

<script>
// قاعدة بيانات الحزم المحاكية للسيناريوهات المختلفة
let packets = [];
let selectedIndex = 0;
let currentFilter = '';
let packetCounter = 1;

const protocolColors = {
    'HTTP': { bg: 'rgba(59, 130, 246, 0.12)', text: '#2563EB' },
    'DNS':  { bg: 'rgba(13, 148, 136, 0.12)', text: '#0D9488' },
    'TCP':  { bg: 'rgba(139, 92, 246, 0.12)', text: '#7C3AED' },
    'ICMP': { bg: 'rgba(245, 158, 11, 0.12)', text: '#D97706' },
    'ARP':  { bg: 'rgba(16, 185, 129, 0.12)', text: '#059669' }
};

function createPacket(time, src, dst, proto, len, info, treeData, hexText, explanation) {
    return {
        id: packetCounter++,
        time: time,
        src: src,
        dst: dst,
        proto: proto,
        len: len,
        info: info,
        tree: treeData,
        hex: hexText,
        explanation: explanation
    };
}

function initDemoPackets() {
    packets = [];
    packetCounter = 1;

    // سيناريو البداية: HTTP Handshake & Request
    packets.push(createPacket('0.000', '192.168.1.50', '93.184.216.34', 'TCP', 66, '54321 → 80 [SYN] Seq=0 Win=65535 Len=0 MSS=1460', [
        { title: 'Frame 1: 66 bytes on wire (528 bits)', items: ['Arrival Time: Oct 13, 2026', 'Frame Length: 66 bytes'] },
        { title: 'Ethernet II, Src: 00:1a:2b:3c:4d:5e, Dst: 00:90:e8:42:11:aa', items: ['Destination: 00:90:e8:42:11:aa (Router Gateway)', 'Source: 00:1a:2b:3c:4d:5e (Host PC)', 'Type: IPv4 (0x0800)'] },
        { title: 'Internet Protocol Version 4, Src: 192.168.1.50, Dst: 93.184.216.34', items: ['Version: 4', 'Header Length: 20 bytes', 'Time to Live (TTL): 64', 'Protocol: TCP (6)', 'Header Checksum: 0x4a2c [Correct]'] },
        { title: 'Transmission Control Protocol, Src Port: 54321, Dst Port: 80, Flags: [SYN]', items: ['Sequence Number: 0 (relative sequence number)', 'Acknowledgment Number: 0', 'Header Length: 32 bytes', 'Flags: 0x002 (SYN)', 'Window Size Value: 65535'] }
    ], `0000   00 90 e8 42 11 aa 00 1a  2b 3c 4d 5e 08 00 45 00   ...B....+<M^..E.
0010   00 34 8f 41 40 00 40 06  6b 91 c0 a8 01 32 5d b8   .4.A@.@.k....2].
0020   d8 22 d4 31 00 50 2a 3e  00 00 00 00 00 00 80 02   .".1.P*>........
0030   ff ff 7f 29 00 00 02 04  05 b4 01 03 03 08 01 01   ...)............
0040   04 02                                              ..`,
    `<strong>تحليل حزمة طلب الاتصال الأولى (TCP SYN):</strong><br>
    هذه هي الخطوة الأولى من مصافحة TCP الثلاثية (3-Way Handshake). يقوم جهاز العميل بتوليد رقم تسلسلي عشوائي (Initial Sequence Number: ISN) مع تفعيل راية SYN = 1 للإعلان عن الرغبة في تأسيس جلسة اتصال موثوقة مع خادم الويب على المنفذ الافتراضي 80.`
    ));

    packets.push(createPacket('0.024', '93.184.216.34', '192.168.1.50', 'TCP', 66, '80 → 54321 [SYN, ACK] Seq=0 Ack=1 Win=65160 Len=0 MSS=1460', [
        { title: 'Frame 2: 66 bytes on wire (528 bits)', items: ['Arrival Time: Oct 13, 2026', 'Frame Length: 66 bytes'] },
        { title: 'Ethernet II, Src: 00:90:e8:42:11:aa, Dst: 00:1a:2b:3c:4d:5e', items: ['Destination: 00:1a:2b:3c:4d:5e', 'Source: 00:90:e8:42:11:aa', 'Type: IPv4 (0x0800)'] },
        { title: 'Internet Protocol Version 4, Src: 93.184.216.34, Dst: 192.168.1.50', items: ['Version: 4', 'Time to Live (TTL): 56', 'Protocol: TCP (6)'] },
        { title: 'Transmission Control Protocol, Src Port: 80, Dst Port: 54321, Flags: [SYN, ACK]', items: ['Sequence Number: 0', 'Acknowledgment Number: 1 (Ack = Client Seq + 1)', 'Flags: 0x012 (SYN, ACK)'] }
    ], `0000   00 1a 2b 3c 4d 5e 00 90  e8 42 11 aa 08 00 45 00   ..+<M^...B....E.
0010   00 34 a1 10 00 00 38 06  32 c2 5d b8 d8 22 c0 a8   .4....8.2.].."...
0020   01 32 00 50 d4 31 7c 99  10 14 2a 3e 00 01 80 12   .2.P.1|...*>....
0030   fe 88 1d 04 00 00 02 04  05 b4 01 01 04 02 01 03   ................
0040   03 07                                              ..`,
    `<strong>تحليل استجابة الخادم بالموافقة (TCP SYN-ACK):</strong><br>
    الخادم يرد بالموافقة ويرسل رقمه التسلسلي الخاص، مع تأكيد استلام طلب العميل بإضافة 1 إلى رقم التسلسل المستلم (Acknowledgment = Seq + 1 = 1). لاحظ تناقص قيمة TTL إلى 56 بسبب مرور الحزمة عبر عدة موجهات.`
    ));

    packets.push(createPacket('0.026', '192.168.1.50', '93.184.216.34', 'TCP', 54, '54321 → 80 [ACK] Seq=1 Ack=1 Win=65535 Len=0', [
        { title: 'Frame 3: 54 bytes on wire (432 bits)', items: ['Status: TCP Handshake Completed successfully'] },
        { title: 'Ethernet II & IPv4', items: ['Src: 192.168.1.50', 'Dst: 93.184.216.34', 'TTL: 64'] },
        { title: 'Transmission Control Protocol', items: ['Flags: [ACK] (0x010)', 'State: ESTABLISHED'] }
    ], `0000   00 90 e8 42 11 aa 00 1a  2b 3c 4d 5e 08 00 45 00   ...B....+<M^..E.
0010   00 28 8f 42 40 00 40 06  6b 9c c0 a8 01 32 5d b8   .(.B@.@.k....2].
0020   d8 22 d4 31 00 50 2a 3e  00 01 7c 99 10 15 50 10   .".1.P*>..|...P.
0030   ff ff a4 12 00 00                                  ......`,
    `<strong>اكتمال مصافحة TCP وتأسيس القناة (ESTABLISHED):</strong><br>
    العميل يؤكد استلام موافقة الخادم. في هذه اللحظة أصبحت القناة مهيأة بالكامل لنقل بيانات التطبيقات (مثل HTTP أو HTTPS أو FTP).`
    ));

    packets.push(createPacket('0.028', '192.168.1.50', '93.184.216.34', 'HTTP', 198, 'GET /index.html HTTP/1.1 (Host: netlearn.pro)', [
        { title: 'Frame 4: 198 bytes on wire', items: ['Arrival Time: Oct 13, 2026', 'Protocols in frame: eth:ip:tcp:http'] },
        { title: 'Ethernet II & IPv4', items: ['Type: IPv4 (0x0800)', 'TTL: 64', 'Protocol: TCP (6)'] },
        { title: 'Transmission Control Protocol', items: ['Src Port: 54321', 'Dst Port: 80', 'Flags: [PSH, ACK]'] },
        { title: 'Hypertext Transfer Protocol', items: ['Request Method: GET', 'Request URI: /index.html', 'Request Version: HTTP/1.1', 'Host: netlearn.pro', 'User-Agent: NetLearn-Student-Engine/2026'] }
    ], `0000   00 90 e8 42 11 aa 00 1a  2b 3c 4d 5e 08 00 45 00   ...B....+<M^..E.
0010   00 b8 8f 43 40 00 40 06  6b 0b c0 a8 01 32 5d b8   ...C@.@.k....2].
0020   d8 22 d4 31 00 50 2a 3e  00 01 7c 99 10 15 50 18   .".1.P*>..|...P.
0030   ff ff f1 0d 00 00 47 45  54 20 2f 69 6e 64 65 78   ......GET /index
0040   2e 68 74 6d 6c 20 48 54  54 50 2f 31 2e 31 0d 0a   .html HTTP/1.1..
0050   48 6f 73 74 3a 20 6e 65  74 6c 65 61 72 6e 2e 70   Host: netlearn.p
0060   72 6f 0d 0a 0d 0a                                  ro....`,
    `<strong>طبقة التطبيقات (Application Layer Payload - HTTP GET):</strong><br>
    تغليف طلب HTTP داخل حزمة TCP عبر المنفذ 80. لاحظ استخدام الراية [PSH] لدفع الحزمة مباشرة إلى معالج الخادم دون انتظار ملء المخزن المؤقت (Buffer).`
    ));

    renderTable();
    selectPacket(0);
}

function generateScenario(type) {
    const baseTime = (packets.length > 0) ? (parseFloat(packets[packets.length - 1].time) + 0.05).toFixed(3) : '0.000';

    if (type === 'http') {
        const p1 = createPacket(baseTime, '192.168.1.50', '93.184.216.34', 'HTTP', 240, 'GET /api/status HTTP/1.1', [
            { title: 'Frame: HTTP API Request', items: ['Length: 240 bytes', 'Protocol: HTTP / JSON'] },
            { title: 'IPv4 Header', items: ['Src: 192.168.1.50', 'Dst: 93.184.216.34', 'TTL: 64'] },
            { title: 'TCP Header', items: ['Dst Port: 80 (HTTP)', 'Flags: [PSH, ACK]'] },
            { title: 'Hypertext Transfer Protocol', items: ['GET /api/status HTTP/1.1', 'Accept: application/json'] }
        ], `0000   00 90 e8 42 11 aa 00 1a  2b 3c 4d 5e 08 00 45 00   ...B....+<M^..E.
0010   00 e2 90 01 40 00 40 06  6a 4d c0 a8 01 32 5d b8   ....@.@.jM...2].
0020   d8 22 d4 32 00 50 2a 40  00 01 7c 99 20 01 50 18   .".2.P*@..|. .P.
0030   ff ff 8b 44 00 00 47 45  54 20 2f 61 70 69 2f 73   ...D..GET /api/s
0040   74 61 74 75 73 20 48 54  54 50 2f 31 2e 31 0d 0a   tatus HTTP/1.1..`,
        'طلب HTTP استدعاء واجهة برمجة تطبيقات REST API للحصول على حالة الخادم.');

        const p2 = createPacket((parseFloat(baseTime) + 0.015).toFixed(3), '93.184.216.34', '192.168.1.50', 'HTTP', 412, 'HTTP/1.1 200 OK (application/json)', [
            { title: 'Frame: HTTP 200 OK Response', items: ['Length: 412 bytes'] },
            { title: 'IPv4 Header', items: ['Src: 93.184.216.34', 'Dst: 192.168.1.50', 'TTL: 56'] },
            { title: 'Hypertext Transfer Protocol', items: ['Status Code: 200 (OK)', 'Content-Type: application/json', 'Payload: {"status":"active","load":"12%"}'] }
        ], `0000   00 1a 2b 3c 4d 5e 00 90  e8 42 11 aa 08 00 45 00   ..+<M^...B....E.
0010   01 8e a2 11 00 00 38 06  31 43 5d b8 d8 22 c0 a8   ......8.1C].."...
0020   01 32 00 50 d4 32 7c 99  20 01 2a 40 00 e3 50 18   .2.P.2|. .*@..P.
0030   fe 88 41 99 00 00 48 54  54 50 2f 31 2e 31 20 32   ..A...HTTP/1.1 2
0040   30 30 20 4f 4b 0d 0a 43  6f 6e 74 65 6e 74 2d 54   00 OK..Content-T`,
        'رد خادم الويب برمز النجاح 200 OK متضمناً بيانات JSON المطلوبة.');

        packets.push(p1, p2);
    } else if (type === 'dns') {
        const p1 = createPacket(baseTime, '192.168.1.50', '8.8.8.8', 'DNS', 74, 'Standard query 0x1a2b A netlearn.pro', [
            { title: 'Frame: DNS Query over UDP', items: ['Length: 74 bytes', 'Transport: UDP Port 53'] },
            { title: 'User Datagram Protocol', items: ['Src Port: 60234', 'Dst Port: 53 (DNS)', 'Length: 40 bytes'] },
            { title: 'Domain Name System (query)', items: ['Transaction ID: 0x1a2b', 'Flags: 0x0100 (Standard query)', 'Questions: 1', 'Query: netlearn.pro: type A, class IN'] }
        ], `0000   00 90 e8 42 11 aa 00 1a  2b 3c 4d 5e 08 00 45 00   ...B....+<M^..E.
0010   00 4a 41 22 00 00 40 11  bb 89 c0 a8 01 32 08 08   .JA"..@......2..
0020   08 08 eb 4a 00 35 00 36  9a 11 1a 2b 01 00 00 01   ...J.5.6...+....
0030   00 00 00 00 00 00 08 6e  65 74 6c 65 61 72 6e 03   .......netlearn.
0040   70 72 6f 00 00 01 00 01                            pro.....`,
        'استعلام نظام أسماء النطاقات (DNS) للبحث عن عنوان IP المقابل لنطاق netlearn.pro عبر منفذ UDP 53.');

        const p2 = createPacket((parseFloat(baseTime) + 0.012).toFixed(3), '8.8.8.8', '192.168.1.50', 'DNS', 90, 'Standard query response 0x1a2b A netlearn.pro A 185.199.108.153', [
            { title: 'Frame: DNS Response', items: ['Length: 90 bytes'] },
            { title: 'Domain Name System (response)', items: ['Transaction ID: 0x1a2b', 'Answers: 1', 'netlearn.pro: type A, class IN, addr 185.199.108.153'] }
        ], `0000   00 1a 2b 3c 4d 5e 00 90  e8 42 11 aa 08 00 45 00   ..+<M^...B....E.
0010   00 5a 21 00 00 00 38 11  e1 ab 08 08 08 08 c0 a8   .Z!...8.........
0020   01 32 00 35 eb 4a 00 46  4f 12 1a 2b 81 80 00 01   .2.5.J.FO..+....
0030   00 01 00 00 00 00 08 6e  65 74 6c 65 61 72 6e 03   .......netlearn.
0040   70 72 6f 00 00 01 00 01  c0 0c 00 01 00 01 00 00   pro.............
0050   01 2c 00 04 b9 c7 6c 99                            .,....l.`,
        'خادم DNS (8.8.8.8) يجيب بالسجل A-Record الذي يحمل العنوان الرقمي للهدف (185.199.108.153).');

        packets.push(p1, p2);
    } else if (type === 'tcp') {
        const p = createPacket(baseTime, '192.168.1.50', '192.168.1.1', 'TCP', 54, '51234 → 22 [SYN] Seq=0 Win=64240 Len=0 MSS=1460 (SSH Attempt)', [
            { title: 'Frame: TCP SYN on Port 22 (SSH)', items: ['Length: 54 bytes'] },
            { title: 'IPv4 Header', items: ['Src: 192.168.1.50', 'Dst: 192.168.1.1 (Gateway)', 'Protocol: TCP (6)'] },
            { title: 'TCP Header', items: ['Src Port: 51234', 'Dst Port: 22 (Secure Shell SSH)', 'Flags: [SYN]'] }
        ], `0000   00 90 e8 42 11 aa 00 1a  2b 3c 4d 5e 08 00 45 00   ...B....+<M^..E.
0010   00 34 32 11 40 00 40 06  c8 9a c0 a8 01 32 c0 a8   .42.@.@.....2..
0020   01 01 c8 22 00 16 11 22  33 44 00 00 00 00 80 02   ..."..."3D......
0030   fa f0 91 12 00 00                                  ......`,
        'محاولة اتصال مشفر SSH مع الراوتر الأساسي على المنفذ 22.');
        packets.push(p);
    } else if (type === 'icmp') {
        const p1 = createPacket(baseTime, '192.168.1.50', '8.8.8.8', 'ICMP', 74, 'Echo (ping) request  id=0x0001, seq=1/256, ttl=64', [
            { title: 'Frame: ICMP Echo Request', items: ['Length: 74 bytes'] },
            { title: 'IPv4 Header', items: ['Protocol: ICMP (1)', 'TTL: 64'] },
            { title: 'Internet Control Message Protocol', items: ['Type: 8 (Echo ping request)', 'Code: 0', 'Checksum: 0x4f12', 'Identifier: 0x0001', 'Sequence Number: 1'] }
        ], `0000   00 90 e8 42 11 aa 00 1a  2b 3c 4d 5e 08 00 45 00   ...B....+<M^..E.
0010   00 3c 71 22 00 00 40 01  8c a1 c0 a8 01 32 08 08   .<q"..@......2..
0020   08 08 08 00 4f 12 00 01  00 01 61 62 63 64 65 66   ....O.....abcdef
0030   67 68 69 6a 6b 6c 6d 6e  6f 70 71 72 73 74 75 76   ghijklmnopqrstuv
0040   77 61 62 63 64 65 66 67                            wabcdefg`,
        'أمر Ping: إرسال حزمة ICMP Type 8 لقياس زمن الذهاب والعودة (Round Trip Time).');

        const p2 = createPacket((parseFloat(baseTime) + 0.009).toFixed(3), '8.8.8.8', '192.168.1.50', 'ICMP', 74, 'Echo (ping) reply    id=0x0001, seq=1/256, ttl=118 (reply in 9.2ms)', [
            { title: 'Frame: ICMP Echo Reply', items: ['Length: 74 bytes'] },
            { title: 'Internet Control Message Protocol', items: ['Type: 0 (Echo ping reply)', 'Code: 0', 'Sequence Number: 1', 'Round Trip Time: 9.2 ms'] }
        ], `0000   00 1a 2b 3c 4d 5e 00 90  e8 42 11 aa 08 00 45 00   ..+<M^...B....E.
0010   00 3c 81 00 00 00 76 01  47 c3 08 08 08 08 c0 a8   .<....v.G.......
0020   01 32 00 00 57 12 00 01  00 01 61 62 63 64 65 66   .2..W.....abcdef
0030   67 68 69 6a 6b 6c 6d 6e  6f 70 71 72 73 74 75 76   ghijklmnopqrstuv
0040   77 61 62 63 64 65 66 67                            wabcdefg`,
        'الرد على أمر Ping بحزمة ICMP Type 0 بنجاح، مما يثبت جاهزية المسار والاتصال.');

        packets.push(p1, p2);
    } else if (type === 'arp') {
        const p1 = createPacket(baseTime, '00:1a:2b:3c:4d:5e', 'Broadcast (ff:ff:ff:ff:ff:ff)', 'ARP', 42, 'Who has 192.168.1.1? Tell 192.168.1.50', [
            { title: 'Frame: ARP Request Broadcast', items: ['Length: 42 bytes', 'Broadcast Frame'] },
            { title: 'Address Resolution Protocol (request)', items: ['Hardware Type: Ethernet (1)', 'Protocol Type: IPv4 (0x0800)', 'Opcode: request (1)', 'Sender MAC: 00:1a:2b:3c:4d:5e', 'Sender IP: 192.168.1.50', 'Target MAC: 00:00:00:00:00:00', 'Target IP: 192.168.1.1'] }
        ], `0000   ff ff ff ff ff ff 00 1a  2b 3c 4d 5e 08 06 00 01   ........+<M^....
0010   08 00 06 04 00 01 00 1a  2b 3c 4d 5e c0 a8 01 32   ........+<M^...2
0020   00 00 00 00 00 00 c0 a8  01 01                     ..........`,
        'بروتوكول تحويل العناوين (ARP): يبث الجهاز استفساراً لجميع الأجهزة لمعرفة MAC Address الخاص ببوابة التوجيه 192.168.1.1.');

        const p2 = createPacket((parseFloat(baseTime) + 0.002).toFixed(3), '00:90:e8:42:11:aa', '00:1a:2b:3c:4d:5e', 'ARP', 42, '192.168.1.1 is at 00:90:e8:42:11:aa', [
            { title: 'Address Resolution Protocol (reply)', items: ['Opcode: reply (2)', 'Sender MAC: 00:90:e8:42:11:aa', 'Sender IP: 192.168.1.1', 'Target MAC: 00:1a:2b:3c:4d:5e'] }
        ], `0000   00 1a 2b 3c 4d 5e 00 90  e8 42 11 aa 08 06 00 01   ..+<M^...B......
0010   08 00 06 04 00 02 00 90  e8 42 11 aa c0 a8 01 01   .........B......
0020   00 1a 2b 3c 4d 5e c0 a8  01 32                     ..+<M^...2`,
        'الراوتر يجيب بعنوان الـ MAC الفيزيائي الخاص به ويتم حفظه في جدول ARP Cache.');

        packets.push(p1, p2);
    }

    renderTable();
    selectPacket(packets.length - 1);
}

function renderTable() {
    const tbody = document.getElementById('packet-tbody');
    tbody.innerHTML = '';

    packets.forEach((pkt, idx) => {
        // فحص الفلتر
        if (currentFilter) {
            const f = currentFilter.toLowerCase();
            const match = pkt.proto.toLowerCase().includes(f) ||
                          pkt.src.toLowerCase().includes(f) ||
                          pkt.dst.toLowerCase().includes(f) ||
                          pkt.info.toLowerCase().includes(f);
            if (!match) return;
        }

        const tr = document.createElement('tr');
        tr.style.cursor = 'pointer';
        tr.style.transition = 'background-color 0.15s ease';
        if (idx === selectedIndex) {
            tr.style.backgroundColor = 'rgba(79, 70, 229, 0.1)';
            tr.style.fontWeight = '700';
        }

        const pStyle = protocolColors[pkt.proto] || { bg: '#E2E8F0', text: '#334155' };

        tr.innerHTML = `
            <td style="text-align: center; color: var(--text-muted);">${pkt.id}</td>
            <td style="text-align: center;">${pkt.time}</td>
            <td style="text-align: left; font-weight: 600;">${pkt.src}</td>
            <td style="text-align: left; font-weight: 600;">${pkt.dst}</td>
            <td style="text-align: center;">
                <span style="background: ${pStyle.bg}; color: ${pStyle.text}; padding: 2px 8px; border-radius: 4px; font-weight: 800; font-size: 0.78rem;">
                    ${pkt.proto}
                </span>
            </td>
            <td style="text-align: center;">${pkt.len}</td>
            <td style="text-align: left; color: var(--text-heading);">${escapeHtml(pkt.info)}</td>
        `;

        tr.onclick = () => selectPacket(idx);
        tbody.appendChild(tr);
    });
}

function selectPacket(index) {
    selectedIndex = index;
    const pkt = packets[index];
    if (!pkt) return;

    document.getElementById('selected-packet-badge').textContent = `حزمة #${pkt.id} (${pkt.proto})`;
    
    // شجرة الترويسات
    const treeEl = document.getElementById('dissection-tree');
    let treeHtml = '';
    pkt.tree.forEach((node, nIdx) => {
        treeHtml += `
            <details open style="margin-bottom: 8px; border-left: 2px solid var(--brand-primary); padding-left: 10px;">
                <summary style="cursor: pointer; font-weight: 700; color: var(--brand-primary); margin-bottom: 4px;">
                    ▶ ${escapeHtml(node.title)}
                </summary>
                <ul style="list-style: none; padding-left: 14px; margin: 4px 0; color: var(--text-body);">
                    ${node.items.map(it => `<li style="padding: 2px 0;">• ${escapeHtml(it)}</li>`).join('')}
                </ul>
            </details>
        `;
    });
    treeEl.innerHTML = treeHtml;

    // مصفوفة الـ Hex
    document.getElementById('hex-dump-view').textContent = pkt.hex;

    // الشرح الأكاديمي
    document.getElementById('field-explanation').innerHTML = pkt.explanation;

    renderTable();
}

function applyFilter() {
    currentFilter = document.getElementById('filter-input').value.trim();
    renderTable();
}

function setFilterChip(proto) {
    document.getElementById('filter-input').value = proto;
    currentFilter = proto;
    document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active', 'btn-primary'));
    if (!proto) {
        document.getElementById('chip-all').classList.add('active');
    } else {
        const chip = document.getElementById('chip-' + proto.toLowerCase());
        if (chip) chip.classList.add('active');
    }
    renderTable();
}

function clearPackets() {
    packets = [];
    selectedIndex = -1;
    document.getElementById('packet-tbody').innerHTML = '';
    document.getElementById('dissection-tree').innerHTML = '<div style="color: var(--text-muted); padding: 20px 0;">تم مسح السجل. اختر سيناريو من الأعلى لتوليد حزم جديدة.</div>';
    document.getElementById('hex-dump-view').textContent = 'No packet selected.';
    document.getElementById('field-explanation').textContent = 'اختر سيناريو لتوليد وفحص الحزم.';
    document.getElementById('selected-packet-badge').textContent = 'فارغ';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// تهيئة البداية
initDemoPackets();
</script>

<?php require_once '../includes/footer.php'; ?>
