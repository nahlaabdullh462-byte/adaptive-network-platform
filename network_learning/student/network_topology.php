<?php
$pageTitle = "محاكي طوبولوجيا الشبكات ومسار الحزم الحي (Packet Flow Topology)";
require_once '../config/security.php';
require_auth('student');
require_once '../includes/header.php';
?>

<div style="margin-bottom: 25px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
        <div>
            <span class="badge badge-cyan" style="margin-bottom: 8px;">
                <i class="fa-solid fa-diagram-project"></i> محاكي حركة الحزم الحية (Interactive Topology Visualizer)
            </span>
            <h1 style="font-size: 1.75rem; color: var(--text-heading); margin-bottom: 6px;">
                محاكي طوبولوجيا الشبكات ومسار تدفق الحزم الحي (Packet Flow Lab)
            </h1>
            <p style="font-size: 0.94rem; color: var(--text-muted); margin: 0; max-width: 820px;">
                شاهد بالرسوم المتحركة التفاعلية كيف تتحرك حزم البيانات (ICMP, HTTP, DNS, ARP) بين الأجهزة الحقيقية، وتمر عبر المبدلات والموجهات والجدران النارية مع مؤثرات صوتية هندسية تحاكي أجهزة الشبكة.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <button type="button" class="btn btn-secondary btn-sm" id="sound-toggle-btn" onclick="toggleAudio()" style="display: flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-volume-high" id="sound-icon"></i>
                <span id="sound-label">الصوت: مفعّل</span>
            </button>
            <a href="dashboard.php" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-house"></i> لوحة التحكم
            </a>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- مسرح طوبولوجيا الشبكات التفاعلي المتحرك (Interactive Topology Canvas)       -->
<!-- ========================================================================= -->
<div class="academic-card" style="margin-bottom: 25px; border-top: 3px solid var(--brand-cyan); padding: 22px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <span class="badge badge-success" id="link-status-badge">
                <i class="fa-solid fa-circle-check"></i> الشبكة متصلة وجاهزة
            </span>
            <span style="font-size: 0.82rem; color: var(--text-muted);">
                <i class="fa-solid fa-hand-pointer"></i> يمكنك النقر على أي جهاز في المخطط لفحص إعداداته الهندسية
            </span>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <!-- اختيار سرعة المحاكاة -->
            <div style="display: flex; align-items: center; gap: 4px; font-size: 0.8rem; color: var(--text-muted); background: rgba(255,255,255,0.04); padding: 4px 8px; border-radius: var(--radius-sm); border: 1px solid var(--border-main);">
                <span>السرعة:</span>
                <select id="sim-speed-select" onchange="setSimSpeed(this.value)" style="background: transparent; color: var(--text-heading); border: none; font-size: 0.8rem; cursor: pointer; outline: none;">
                    <option value="0.015">0.5x بطيء</option>
                    <option value="0.03" selected>1.0x عادي</option>
                    <option value="0.06">2.0x سريع</option>
                </select>
            </div>

            <button type="button" class="btn btn-primary btn-sm" onclick="sendPacketSimulation('ping')">
                <i class="fa-solid fa-paper-plane"></i> ICMP Ping
            </button>
            <button type="button" class="btn btn-cyan btn-sm" onclick="sendPacketSimulation('http')">
                <i class="fa-solid fa-globe"></i> HTTP GET (Port 80)
            </button>
            <button type="button" class="btn btn-outline btn-sm" style="border-color: #8B5CF6; color: #C4B5FD;" onclick="sendPacketSimulation('dns')">
                <i class="fa-solid fa-server"></i> DNS Query (UDP 53)
            </button>
            <button type="button" class="btn btn-danger btn-sm" onclick="toggleLinkFailure()">
                <i class="fa-solid fa-link-slash"></i> <span id="link-fail-text">تعطيل مسار الـ WAN</span>
            </button>
        </div>
    </div>

    <!-- لوحة الكانفاس الرسومية لطوبولوجيا الشبكة -->
    <div style="background: #060913; border: 1.5px solid rgba(56, 189, 248, 0.25); border-radius: var(--radius-md); overflow: hidden; position: relative; box-shadow: inset 0 0 30px rgba(0, 0, 0, 0.8); cursor: crosshair;">
        <canvas id="topology-canvas" width="960" height="420" style="width: 100%; height: auto; display: block;"></canvas>
        <div id="canvas-tooltip" style="position: absolute; display: none; background: rgba(15,23,42,0.95); border: 1px solid var(--brand-primary); padding: 8px 12px; border-radius: 6px; font-size: 0.78rem; color: #FFF; pointer-events: none; z-index: 10; box-shadow: 0 4px 12px rgba(0,0,0,0.5);"></div>
    </div>

    <!-- شريط معلومات الحزمة الحية -->
    <div style="margin-top: 16px; background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-main); padding: 14px 18px; border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; font-size: 0.88rem;">
        <div>
            <strong style="color: var(--brand-cyan); margin-left: 6px;">حالة الحزمة الحالية:</strong>
            <span id="packet-status-text" style="color: #FFFFFF;">بانتظار انطلاق الحزمة... اختر نوع الاختبار أعلاه</span>
        </div>
        <div class="code-font" style="color: var(--text-muted); display: flex; gap: 14px;">
            <span>RTT: <strong id="rtt-display" style="color: #34D399;">-- ms</strong></span>
            <span>TTL: <strong id="ttl-display" style="color: #38BDF8;">64</strong></span>
            <span>Packet Type: <strong id="proto-display" style="color: #F59E0B;">--</strong></span>
        </div>
    </div>

    <!-- سجل التتبع الحي (Live Hop Traceroute Log) -->
    <div style="margin-top: 14px; background: rgba(0, 0, 0, 0.4); border: 1px solid rgba(255,255,255,0.06); border-radius: var(--radius-sm); padding: 12px 16px;">
        <div style="font-size: 0.82rem; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; display: flex; justify-content: space-between;">
            <span><i class="fa-solid fa-list-check" style="color: var(--brand-cyan);"></i> سجل تتبع القفزات اللحظي (Hop-by-Hop Trace)</span>
            <button type="button" onclick="clearTraceLog()" style="background:none;border:none;color:var(--text-muted);font-size:0.75rem;cursor:pointer;">مسح السجل</button>
        </div>
        <div id="hop-trace-log" style="font-family: 'JetBrains Mono', monospace; font-size: 0.78rem; color: #CBD5E1; max-height: 100px; overflow-y: auto; line-height: 1.6;">
            <div style="color: #64748B;">-- لا يوجد حركة حزم حالية. اضغط على أحد أزرار الإرسال أعلاه لمشاهدة تحليل التدفق --</div>
        </div>
    </div>

</div>

<!-- نافذة فحص الجهاز المنبثقة (Device Inspector Modal) -->
<div id="device-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #0F172A; border: 1.5px solid var(--brand-cyan); border-radius: var(--radius-md); max-width: 500px; width: 100%; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.8); animation: slideUpFade 0.2s ease;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border-main); padding-bottom: 10px;">
            <h3 id="dev-modal-title" style="margin: 0; color: #FFF; font-size: 1.15rem; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-microchip" style="color: var(--brand-cyan);"></i> تفاصيل الجهاز
            </h3>
            <button type="button" onclick="closeDeviceModal()" style="background: transparent; border: none; color: #94A3B8; font-size: 1.2rem; cursor: pointer;">&times;</button>
        </div>
        <div id="dev-modal-body" style="font-size: 0.88rem; line-height: 1.7; color: #CBD5E1;">
            <!-- يُحقن ديناميكياً -->
        </div>
        <div style="margin-top: 18px; text-align: left;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeDeviceModal()">إغلاق</button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- جداول الأجهزة والقفزات الهندسية (Routing & MAC Inspect)                   -->
<!-- ========================================================================= -->
<div class="grid-2" style="gap: 25px;">
    
    <div class="academic-card" style="padding: 22px;">
        <h3 style="font-size: 1.1rem; color: var(--text-heading); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-route" style="color: var(--brand-primary);"></i>
            جدول توجيه الراوتر المركزي (Core Router Routing Table)
        </h3>
        
        <table class="academic-table" style="font-size: 0.84rem; margin: 0;">
            <thead>
                <tr>
                    <th>الشبكة الهدف</th>
                    <th>القناع</th>
                    <th>المنفذ / القفزة</th>
                    <th>البروتوكول</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="code-font">192.168.1.0</td>
                    <td class="code-font">/24</td>
                    <td class="code-font">GigabitEthernet0/0</td>
                    <td><span class="badge badge-success">Direct (C)</span></td>
                </tr>
                <tr>
                    <td class="code-font">172.16.0.0</td>
                    <td class="code-font">/24</td>
                    <td class="code-font">GigabitEthernet0/1</td>
                    <td><span class="badge badge-primary">OSPF (O)</span></td>
                </tr>
                <tr>
                    <td class="code-font">0.0.0.0</td>
                    <td class="code-font">/0</td>
                    <td class="code-font">via 10.0.0.2 (Firewall)</td>
                    <td><span class="badge badge-secondary">Default (S*)</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="academic-card" style="padding: 22px;">
        <h3 style="font-size: 1.1rem; color: var(--text-heading); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-microchip" style="color: var(--brand-cyan);"></i>
            جدول عناوين المبدل (Switch MAC Address Table)
        </h3>
        
        <table class="academic-table" style="font-size: 0.84rem; margin: 0;">
            <thead>
                <tr>
                    <th>المنفذ (Port)</th>
                    <th>عنوان MAC المسجل</th>
                    <th>الشبكة الافتراضية (VLAN)</th>
                    <th>النوع</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="code-font">Fa0/1</td>
                    <td class="code-font">00:1A:2B:3C:4D:01 (PC1)</td>
                    <td><span class="badge badge-primary">VLAN 10</span></td>
                    <td>Dynamic</td>
                </tr>
                <tr>
                    <td class="code-font">Fa0/2</td>
                    <td class="code-font">00:1A:2B:3C:4D:02 (PC2)</td>
                    <td><span class="badge badge-primary">VLAN 10</span></td>
                    <td>Dynamic</td>
                </tr>
                <tr>
                    <td class="code-font">Gi0/1</td>
                    <td class="code-font">00:1A:2B:EE:FF:01 (Core Router)</td>
                    <td><span class="badge badge-cyan">Trunk (802.1Q)</span></td>
                    <td>Static</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<!-- تضمين مكتبة الصوت المهنية -->
<script src="../assets/js/sound_effects.js"></script>

<script>
const canvas = document.getElementById('topology-canvas');
const ctx = canvas.getContext('2d');

let isLinkBroken = false;
let activePackets = [];
let packetSpeed = 0.03;

// إدارة حالة الصوت
function toggleAudio() {
    const isMuted = SoundFX.toggleMute();
    updateSoundUI(isMuted);
    if (!isMuted) SoundFX.play('click');
}

function updateSoundUI(isMuted) {
    const icon = document.getElementById('sound-icon');
    const label = document.getElementById('sound-label');
    if (isMuted) {
        icon.className = 'fa-solid fa-volume-xmark';
        label.textContent = 'الصوت: مكتوم';
    } else {
        icon.className = 'fa-solid fa-volume-high';
        label.textContent = 'الصوت: مفعّل';
    }
}
updateSoundUI(SoundFX.getMuted());

function setSimSpeed(speedVal) {
    packetSpeed = parseFloat(speedVal);
    SoundFX.play('click');
}

// تعريف أجهزة الشبكة ومواقعها وبياناتها الهندسية
const nodes = [
    { 
        id: 'pc1', 
        name: 'PC 1 (Engineering)', 
        ip: '192.168.1.10', 
        mac: '00:1A:2B:3C:4D:01', 
        mask: '255.255.255.0', 
        gateway: '192.168.1.1',
        vlan: 'VLAN 10 (Sales)',
        x: 100, 
        y: 140, 
        type: 'pc' 
    },
    { 
        id: 'pc2', 
        name: 'PC 2 (Marketing)', 
        ip: '192.168.1.20', 
        mac: '00:1A:2B:3C:4D:02', 
        mask: '255.255.255.0', 
        gateway: '192.168.1.1',
        vlan: 'VLAN 10 (Sales)',
        x: 100, 
        y: 290, 
        type: 'pc' 
    },
    { 
        id: 'sw1', 
        name: 'Switch 1 (Access L2)', 
        ip: '192.168.1.2 (SVI)', 
        mac: '00:1A:2B:99:88:01', 
        mask: '255.255.255.0', 
        gateway: '192.168.1.1',
        vlan: 'Trunk 802.1Q Active',
        x: 290, 
        y: 215, 
        type: 'switch' 
    },
    { 
        id: 'r1', 
        name: 'Core Router (Cisco 2911)', 
        ip: '192.168.1.1 / 10.0.0.1', 
        mac: '00:1A:2B:EE:FF:01', 
        mask: 'Gig0/0: /24, Gig0/1: /30', 
        gateway: '10.0.0.2',
        vlan: 'Router-on-a-Stick',
        x: 500, 
        y: 215, 
        type: 'router' 
    },
    { 
        id: 'fw', 
        name: 'Firewall (NextGen ASA)', 
        ip: '10.0.0.2 / 172.16.0.1', 
        mac: '00:1A:2B:AA:BB:01', 
        mask: 'Inside: /30, Outside: /24', 
        gateway: '172.16.0.100',
        vlan: 'DMZ Security Zone',
        x: 700, 
        y: 215, 
        type: 'firewall' 
    },
    { 
        id: 'srv', 
        name: 'Web & DNS Server', 
        ip: '172.16.0.100', 
        mac: '00:1A:2B:DD:EE:99', 
        mask: '255.255.255.0', 
        gateway: '172.16.0.1',
        vlan: 'VLAN 50 (Server Farm)',
        x: 880, 
        y: 215, 
        type: 'server' 
    }
];

// روابط الاتصال بين الأجهزة
const links = [
    { from: 'pc1', to: 'sw1', label: 'Fa0/1' },
    { from: 'pc2', to: 'sw1', label: 'Fa0/2' },
    { from: 'sw1', to: 'r1', label: 'Gig0/1' },
    { from: 'r1', to: 'fw', label: 'Gig0/2' },
    { from: 'fw', to: 'srv', label: 'Gig0/3' }
];

function getNode(id) {
    return nodes.find(n => n.id === id);
}

// إضافة سطر في سجل التتبع اللحظي
function appendTraceLog(text, color = '#CBD5E1') {
    const logBox = document.getElementById('hop-trace-log');
    const time = new Date().toLocaleTimeString('ar-EG', { hour12: false });
    const line = document.createElement('div');
    line.style.color = color;
    line.innerHTML = `<span style="color:#64748B;">[${time}]</span> ${text}`;
    logBox.appendChild(line);
    logBox.scrollTop = logBox.scrollHeight;
}

function clearTraceLog() {
    document.getElementById('hop-trace-log').innerHTML = '<div style="color: #64748B;">-- تم مسح السجل --</div>';
}

// دالة الرسم الأساسية المستمرة
function renderTopology() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    // 1. رسم شبكة الخلفية الهندسية
    ctx.strokeStyle = 'rgba(56, 189, 248, 0.05)';
    ctx.lineWidth = 1;
    for (let x = 0; x < canvas.width; x += 30) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, canvas.height);
        ctx.stroke();
    }
    for (let y = 0; y < canvas.height; y += 30) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(canvas.width, y);
        ctx.stroke();
    }

    // 2. رسم الروابط بين الأجهزة
    links.forEach(l => {
        const n1 = getNode(l.from);
        const n2 = getNode(l.to);
        const isBroken = isLinkBroken && ((l.from === 'r1' && l.to === 'fw') || (l.from === 'fw' && l.to === 'srv'));

        ctx.beginPath();
        ctx.moveTo(n1.x, n1.y);
        ctx.lineTo(n2.x, n2.y);
        ctx.lineWidth = 2.5;
        if (isBroken) {
            ctx.strokeStyle = '#EF4444';
            ctx.setLineDash([6, 6]);
        } else {
            ctx.strokeStyle = 'rgba(56, 189, 248, 0.45)';
            ctx.setLineDash([]);
        }
        ctx.stroke();
        ctx.setLineDash([]);

        // تسمية المنفذ في المنتصف
        const mx = (n1.x + n2.x) / 2;
        const my = (n1.y + n2.y) / 2 - 8;
        ctx.fillStyle = '#64748B';
        ctx.font = '9px JetBrains Mono, monospace';
        ctx.textAlign = 'center';
        ctx.fillText(l.label, mx, my);
    });

    // 3. رسم الحزم المتحركة
    for (let i = activePackets.length - 1; i >= 0; i--) {
        const p = activePackets[i];
        p.progress += packetSpeed;

        if (p.progress >= 1) {
            p.stepIndex++;
            p.progress = 0;

            if (p.stepIndex >= p.path.length - 1) {
                // وصول الحزمة بنجاح
                SoundFX.play('success');
                const rtt = (Math.random() * 4 + 7).toFixed(1);
                document.getElementById('rtt-display').textContent = rtt + ' ms';
                document.getElementById('packet-status-text').innerHTML = '<span style="color:#10B981;font-weight:700;"><i class="fa-solid fa-circle-check"></i> وصلت الحزمة بنجاح إلى الهدف (' + getNode(p.path[p.path.length-1]).name + ') وتم استلام ACK!</span>';
                appendTraceLog(`✔ استجابة ناجحة: وصلت حزمة [${p.type.toUpperCase()}] بنجاح إلى الهدف. RTT=${rtt}ms`, '#34D399');
                activePackets.splice(i, 1);
                continue;
            } else {
                // القفز إلى عقدة تالية
                const reachedNode = getNode(p.path[p.stepIndex]);
                if (reachedNode.type === 'firewall') {
                    SoundFX.play('firewall');
                    appendTraceLog(`🛡️ الجدار الناري (${reachedNode.name}): فحص الحزمة ومطابقتها لقواعد Access-List -> السماح بالمرور (PERMIT)`, '#F59E0B');
                } else if (reachedNode.type === 'router') {
                    SoundFX.play('hop');
                    appendTraceLog(`🔄 الراوتر (${reachedNode.name}): فحص جدول التوجيه L3 وتحديث عنوان Next-Hop وتخفيض TTL`, '#38BDF8');
                } else {
                    SoundFX.play('hop');
                    appendTraceLog(`⚡ المبدل (${reachedNode.name}): إعادة توجيه L2 بناء على جدول MAC Address Table`, '#94A3B8');
                }
            }
        }

        const currNode = getNode(p.path[p.stepIndex]);
        const nextNode = getNode(p.path[p.stepIndex + 1]);

        // التحقق من انقطاع الرابط
        if (isLinkBroken && ((currNode.id === 'r1' && nextNode.id === 'fw') || (currNode.id === 'fw' && nextNode.id === 'srv'))) {
            SoundFX.play('alert');
            document.getElementById('packet-status-text').innerHTML = '<span style="color:#EF4444;font-weight:700;"><i class="fa-solid fa-triangle-exclamation"></i> تم إسقاط الحزمة (Packet Dropped) بسبب انقطاع مسار WAN!</span>';
            appendTraceLog(`❌ إسقاط الحزمة (Packet Drop): الرابط بين [${currNode.name}] و [${nextNode.name}] مقطوع! (Destination Unreachable)`, '#EF4444');
            activePackets.splice(i, 1);
            continue;
        }

        const px = currNode.x + (nextNode.x - currNode.x) * p.progress;
        const py = currNode.y + (nextNode.y - currNode.y) * p.progress;

        // توهج الحزمة بألوان البروتوكول
        let pColor = '#00F0FF';
        if (p.type === 'http') pColor = '#10B981';
        if (p.type === 'dns') pColor = '#A855F7';
        if (p.type === 'arp') pColor = '#F59E0B';

        ctx.beginPath();
        ctx.arc(px, py, 8, 0, Math.PI * 2);
        ctx.fillStyle = pColor;
        ctx.shadowColor = pColor;
        ctx.shadowBlur = 16;
        ctx.fill();
        ctx.shadowBlur = 0;

        document.getElementById('packet-status-text').textContent = `الحزمة [${p.type.toUpperCase()}] تعبر الآن من ${currNode.name} إلى ${nextNode.name}...`;
    }

    // 4. رسم العُقد وأيقوناتها
    nodes.forEach(n => {
        // دائرة خلفية العقدة
        ctx.beginPath();
        ctx.arc(n.x, n.y, 24, 0, Math.PI * 2);
        ctx.fillStyle = '#0F172A';
        ctx.strokeStyle = n.type === 'router' ? '#3B82F6' : (n.type === 'firewall' ? '#F59E0B' : (n.type === 'server' ? '#10B981' : '#06B6D4'));
        ctx.lineWidth = 2.5;
        ctx.shadowColor = ctx.strokeStyle;
        ctx.shadowBlur = 10;
        ctx.fill();
        ctx.stroke();
        ctx.shadowBlur = 0;

        // رسم شارة نوع الجهاز
        ctx.fillStyle = '#FFFFFF';
        ctx.font = 'bold 11px JetBrains Mono, monospace';
        ctx.textAlign = 'center';
        let badgeText = 'PC';
        if (n.type === 'switch') badgeText = 'SW';
        if (n.type === 'router') badgeText = 'RT';
        if (n.type === 'firewall') badgeText = 'FW';
        if (n.type === 'server') badgeText = 'SRV';
        ctx.fillText(badgeText, n.x, n.y + 4);

        // الاسم والعنوان
        ctx.fillStyle = '#F8FAFC';
        ctx.font = 'bold 12px IBM Plex Sans Arabic, sans-serif';
        ctx.fillText(n.name, n.x, n.y + 40);

        ctx.fillStyle = '#94A3B8';
        ctx.font = '10px JetBrains Mono, monospace';
        ctx.fillText(n.ip, n.x, n.y + 54);
    });

    requestAnimationFrame(renderTopology);
}

// إرسال الحزمة واختبار البروتوكولات
function sendPacketSimulation(type) {
    if (activePackets.length > 0) return;

    SoundFX.play('packet');

    let path = ['pc1', 'sw1', 'r1', 'fw', 'srv'];
    let protoName = 'ICMP Echo Request';
    let ttl = 64;

    if (type === 'http') {
        protoName = 'HTTP TCP:80 (GET /)';
        ttl = 64;
    } else if (type === 'dns') {
        protoName = 'DNS Query (UDP 53)';
        path = ['pc2', 'sw1', 'r1', 'fw', 'srv'];
        ttl = 128;
    } else if (type === 'arp') {
        protoName = 'ARP Who-Has 192.168.1.1?';
        path = ['pc1', 'sw1'];
        ttl = 1;
    }

    activePackets.push({
        type: type,
        path: path,
        stepIndex: 0,
        progress: 0
    });

    document.getElementById('rtt-display').textContent = 'Calculating...';
    document.getElementById('ttl-display').textContent = ttl;
    document.getElementById('proto-display').textContent = protoName;
    document.getElementById('packet-status-text').textContent = `انطلقت حزمة [${protoName}] من المصدر...`;

    appendTraceLog(`🚀 انطلاق حزمة جديدة [${protoName}] من [${getNode(path[0]).name}] متجهة إلى [${getNode(path[path.length-1]).name}]`, '#38BDF8');
}

// تبديل حالة عطل المسار (Link Failure)
function toggleLinkFailure() {
    isLinkBroken = !isLinkBroken;
    const badge = document.getElementById('link-status-badge');
    const btnText = document.getElementById('link-fail-text');

    if (isLinkBroken) {
        SoundFX.play('alert');
        badge.className = 'badge badge-danger';
        badge.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> المسار معطل (Link Failure)';
        btnText.textContent = 'إصلاح مسار الـ WAN';
        appendTraceLog(`⚠️ إنذار: تم قطع كابل التوصيل بين الراوتر والجدار الناري (Interface Serial Down)`, '#EF4444');
    } else {
        SoundFX.play('success');
        badge.className = 'badge badge-success';
        badge.innerHTML = '<i class="fa-solid fa-circle-check"></i> الشبكة متصلة وجاهزة';
        btnText.textContent = 'تعطيل مسار الـ WAN';
        appendTraceLog(`✅ تم إصلاح المسار واستعادة اتصال الـ WAN بنجاح (Line Protocol Up)`, '#34D399');
    }
}

// التفاعل بالنقر على الأجهزة لفحص التفاصيل
canvas.addEventListener('click', function(e) {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;
    const clickX = (e.clientX - rect.left) * scaleX;
    const clickY = (e.clientY - rect.top) * scaleY;

    // البحث عن جهاز تم النقر عليه
    const clickedNode = nodes.find(n => {
        const dx = clickX - n.x;
        const dy = clickY - n.y;
        return Math.sqrt(dx * dx + dy * dy) <= 28;
    });

    if (clickedNode) {
        SoundFX.play('click');
        openDeviceModal(clickedNode);
    }
});

function openDeviceModal(node) {
    const modal = document.getElementById('device-modal');
    document.getElementById('dev-modal-title').innerHTML = `<i class="fa-solid fa-microchip" style="color: var(--brand-cyan);"></i> تفاصيل الجهاز: ${node.name}`;
    
    document.getElementById('dev-modal-body').innerHTML = `
        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-main); border-radius: 8px; padding: 14px; margin-bottom: 12px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span style="color: #94A3B8;">نوع الجهاز (Device Role):</span>
                <strong style="color: #38BDF8; text-transform: uppercase;">${node.type}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span style="color: #94A3B8;">عنوان الـ IP المنطقي:</span>
                <strong class="code-font" style="color: #34D399;">${node.ip}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span style="color: #94A3B8;">قناع الشبكة (Subnet Mask):</span>
                <strong class="code-font" style="color: #CBD5E1;">${node.mask}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span style="color: #94A3B8;">العنوان الفيزيائي (MAC):</span>
                <strong class="code-font" style="color: #F59E0B;">${node.mac}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span style="color: #94A3B8;">البوابة الافتراضية (Gateway):</span>
                <strong class="code-font" style="color: #CBD5E1;">${node.gateway}</strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: #94A3B8;">عضوية الشبكة (VLAN / Zone):</span>
                <strong style="color: #A855F7;">${node.vlan}</strong>
            </div>
        </div>
        <p style="font-size: 0.82rem; color: #94A3B8; margin: 0;">
            <i class="fa-solid fa-circle-info" style="color: var(--brand-cyan);"></i> 
            يتم فحص وتحديث هذه القيم في رأس الحزمة (Layer 2 Frame & Layer 3 Packet) أثناء انتقال البيانات عبر المنافذ.
        </p>
    `;

    modal.style.display = 'flex';
}

function closeDeviceModal() {
    document.getElementById('device-modal').style.display = 'none';
}

// تشغيل المحاكاة
renderTopology();
</script>

<?php require_once '../includes/footer.php'; ?>
