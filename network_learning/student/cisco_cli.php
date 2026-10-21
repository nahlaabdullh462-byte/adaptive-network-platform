<?php
$pageTitle = "محاكي موجه أوامر سيسكو التفاعلي (Cisco IOS CLI Simulator)";
require_once '../config/security.php';
require_auth('student');
require_once '../includes/header.php';
?>

<div style="margin-bottom: 25px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
        <div>
            <span class="badge badge-cyan" style="margin-bottom: 8px;">
                <i class="fa-solid fa-terminal"></i> بيئة محاكاة أنظمة تشغيل سيسكو (Cisco IOS Emulator)
            </span>
            <h1 style="font-size: 1.75rem; color: var(--text-heading); margin-bottom: 6px;">
                محاكي موجه أوامر سيسكو التفاعلي (Cisco IOS CLI Lab)
            </h1>
            <p style="font-size: 0.94rem; color: var(--text-muted); margin: 0; max-width: 800px;">
                تدرب على كتابة أوامر سيسكو الحقيقية المعتمدة في اختبار Cisco CCNA (مثل ضبط المنافذ، العناوين، مسارات التوجيه، وأوامر Show) مع دعم الإكمال التلقائي وسجل الأوامر.
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
<!-- التحديات والمهام المعملية الموجهة (CCNA Lab Challenges)                   -->
<!-- ========================================================================= -->
<div class="academic-card" style="margin-bottom: 25px; border-top: 3px solid var(--brand-cyan); padding: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
        <h3 style="font-size: 1.15rem; color: var(--text-heading); margin: 0; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-list-check" style="color: var(--brand-cyan);"></i>
            التحديات المعملية الموجهة (Interactive CCNA Lab Tasks)
        </h3>
        <span id="lab-badge-status" class="badge badge-warning">
            <i class="fa-solid fa-spinner fa-spin"></i> قيد الإنجاز
        </span>
    </div>

    <div class="grid-3" style="gap: 14px;">
        <div id="card-lab-1" class="calc-metric-box" style="cursor: pointer; border: 1.5px solid var(--brand-primary); background: rgba(59, 130, 246, 0.1);" onclick="loadLabScenario(1)">
            <div style="font-size: 0.78rem; color: var(--brand-cyan); font-weight: 700; margin-bottom: 4px;">المعمل رقم #1 (الأساسي):</div>
            <strong style="color: #FFFFFF; font-size: 0.95rem; display: block; margin-bottom: 4px;">تسمية الراوتر (Set Hostname)</strong>
            <span style="font-size: 0.8rem; color: var(--text-muted);">الأمر المطلوب: <code class="code-font">hostname Core-R1</code></span>
        </div>

        <div id="card-lab-2" class="calc-metric-box" style="cursor: pointer;" onclick="loadLabScenario(2)">
            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 700; margin-bottom: 4px;">المعمل رقم #2 (المنافذ):</div>
            <strong style="color: #FFFFFF; font-size: 0.95rem; display: block; margin-bottom: 4px;">ضبط IP وتشغيل المنفذ (IP & No Shutdown)</strong>
            <span style="font-size: 0.8rem; color: var(--text-muted);">على منفذ: <code class="code-font">g0/0</code> مع عنوان <code class="code-font">192.168.10.1/24</code></span>
        </div>

        <div id="card-lab-3" class="calc-metric-box" style="cursor: pointer;" onclick="loadLabScenario(3)">
            <div style="font-size: 0.78rem; color: var(--text-muted); font-weight: 700; margin-bottom: 4px;">المعمل رقم #3 (التوجيه):</div>
            <strong style="color: #FFFFFF; font-size: 0.95rem; display: block; margin-bottom: 4px;">إضافة مسار افتراضي (Default Route)</strong>
            <span style="font-size: 0.8rem; color: var(--text-muted);">الأمر: <code class="code-font">ip route 0.0.0.0 0.0.0.0 192.168.10.254</code></span>
        </div>
    </div>

    <div id="lab-instruction-banner" style="margin-top: 16px; padding: 12px 18px; background: rgba(6, 182, 212, 0.08); border-radius: var(--radius-sm); border: 1px solid var(--brand-cyan-border); font-size: 0.9rem; color: var(--text-heading); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
        <div>
            <i class="fa-solid fa-circle-info" style="color: var(--brand-cyan); margin-left: 8px;"></i>
            <span id="lab-task-desc">المهمة الحالية: ادخل إلى وضع الإعداد العام وغير اسم الراوتر إلى <strong>Core-R1</strong> عبر أمر <code>hostname Core-R1</code>.</span>
        </div>
        <button type="button" class="btn btn-cyan btn-sm" onclick="checkCurrentLabObjective()">
            <i class="fa-solid fa-vial-circle-check"></i> تحقق من إنجاز المهمة
        </button>
    </div>
</div>

<!-- ========================================================================= -->
<!-- نافذة موجه أوامر سيسكو التفاعلية (Cisco IOS Terminal)                     -->
<!-- ========================================================================= -->
<div class="cisco-terminal" style="margin-bottom: 25px;">
    
    <div class="cisco-terminal-header">
        <div class="cisco-terminal-dots">
            <span style="background: #EF4444;"></span>
            <span style="background: #F59E0B;"></span>
            <span style="background: #10B981;"></span>
        </div>
        <div style="font-size: 0.82rem; color: #94A3B8; font-weight: 600;">
            <i class="fa-solid fa-server" style="color: var(--brand-cyan); margin-right: 6px;"></i>
            Cisco IOS Software, C2900 Software (C2900-UNIVERSALK9-M), Version 15.7(4)M, RELEASE SOFTWARE
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="button" onclick="clearTerminalScreen()" class="btn btn-ghost btn-sm" style="color: #94A3B8; font-size: 0.78rem; padding: 3px 8px;">
                <i class="fa-solid fa-trash-can"></i> مسح
            </button>
            <button type="button" onclick="resetRouterToDefaults()" class="btn btn-ghost btn-sm" style="color: #F87171; font-size: 0.78rem; padding: 3px 8px;">
                <i class="fa-solid fa-arrows-rotate"></i> إعادة تعيين
            </button>
        </div>
    </div>

    <div id="terminal-screen" class="cisco-terminal-body" onclick="focusCliInput()">
        <div style="color: #94A3B8; margin-bottom: 12px; font-size: 0.84rem; line-height: 1.5;">
            * Cisco IOS CLI Simulator initialized.<br>
            * Type <span style="color: #38BDF8; font-weight: bold;">enable</span> to enter Privileged EXEC mode.<br>
            * Type <span style="color: #38BDF8; font-weight: bold;">?</span> or <span style="color: #38BDF8; font-weight: bold;">help</span> for available commands at any mode.<br>
            * Press <span style="color: #38BDF8; font-weight: bold;">Tab</span> to autocomplete, <span style="color: #38BDF8; font-weight: bold;">Up/Down</span> for command history.<br>
            --------------------------------------------------------------------------------
        </div>
        <div id="terminal-history"></div>
        <div style="display: flex; align-items: center; margin-top: 4px;">
            <span id="terminal-prompt" style="color: #34D399; font-weight: bold; white-space: nowrap; margin-right: 6px;">Router&gt;</span>
            <input type="text" id="cli-input" autocomplete="off" spellcheck="false" style="flex: 1; background: transparent; border: none; outline: none; color: #F8FAFC; font-family: var(--font-mono); font-size: 0.95rem; font-weight: 600; caret-color: #38BDF8;">
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- جدول الأوامر المرجعية السريعة (Cisco CCNA Quick Reference)                 -->
<!-- ========================================================================= -->
<div class="academic-card" style="padding: 22px;">
    <h3 style="font-size: 1.1rem; color: var(--text-heading); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-book-bookmark" style="color: var(--brand-primary);"></i>
        دليل أوامر سيسكو السريعة المدعومة في المحاكي (Quick Command Reference)
    </h3>

    <div class="grid-3" style="gap: 14px; font-size: 0.86rem;">
        <div style="background: rgba(15, 23, 42, 0.6); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);">
            <strong style="color: var(--brand-cyan); display: block; margin-bottom: 6px;">أوامر التنقل والتبديل:</strong>
            <ul style="padding-right: 18px; margin: 0; color: var(--text-muted); line-height: 1.8;">
                <li><code>enable</code> (الدخول لوضع الامتيازات #)</li>
                <li><code>configure terminal</code> (وضع الإعداد العام)</li>
                <li><code>interface g0/0</code> (وضع إعداد المنفذ)</li>
                <li><code>exit</code> / <code>end</code> (العودة للوضع السابق)</li>
            </ul>
        </div>

        <div style="background: rgba(15, 23, 42, 0.6); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);">
            <strong style="color: var(--brand-primary); display: block; margin-bottom: 6px;">أوامر الإعداد والشبكة:</strong>
            <ul style="padding-right: 18px; margin: 0; color: var(--text-muted); line-height: 1.8;">
                <li><code>hostname [name]</code> (تغيير اسم الراوتر)</li>
                <li><code>ip address [ip] [mask]</code> (تعيين عنوان)</li>
                <li><code>no shutdown</code> (تشغيل المنفذ)</li>
                <li><code>ip route [net] [mask] [next-hop]</code> (توجيه)</li>
            </ul>
        </div>

        <div style="background: rgba(15, 23, 42, 0.6); padding: 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-subtle);">
            <strong style="color: #34D399; display: block; margin-bottom: 6px;">أوامر الفحص والاستعلام (Show & Ping):</strong>
            <ul style="padding-right: 18px; margin: 0; color: var(--text-muted); line-height: 1.8;">
                <li><code>show ip interface brief</code> (فحص المنافذ)</li>
                <li><code>show ip route</code> (جدول التوجيه)</li>
                <li><code>show running-config</code> (الإعدادات الحالية)</li>
                <li><code>ping [ip]</code> (اختبار الاتصال)</li>
            </ul>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- محرك محاكاة موجه أوامر سيسكو بلغة جافاسكريبت (Cisco IOS Engine)             -->
<!-- ========================================================================= -->
<script>
// حالة الراوتر الافتراضية
let routerState = {
    hostname: 'Router',
    mode: 'user', // 'user', 'privileged', 'config', 'config-if'
    currentInterface: null,
    interfaces: {
        'GigabitEthernet0/0': { ip: null, mask: null, status: 'administratively down', protocol: 'down' },
        'GigabitEthernet0/1': { ip: null, mask: null, status: 'administratively down', protocol: 'down' },
        'FastEthernet0/0': { ip: null, mask: null, status: 'administratively down', protocol: 'down' }
    },
    routes: [
        { dest: '127.0.0.1/32', nextHop: 'directly connected', type: 'Local' }
    ],
    commandHistory: [],
    historyIndex: -1,
    currentLab: 1
};

const inputEl = document.getElementById('cli-input');
const promptEl = document.getElementById('terminal-prompt');
const historyEl = document.getElementById('terminal-history');
const screenEl = document.getElementById('terminal-screen');

function updatePrompt() {
    let p = routerState.hostname;
    if (routerState.mode === 'user') p += '>';
    else if (routerState.mode === 'privileged') p += '#';
    else if (routerState.mode === 'config') p += '(config)#';
    else if (routerState.mode === 'config-if') p += `(config-if)#`;
    promptEl.textContent = p + ' ';
}

function focusCliInput() {
    if (inputEl) inputEl.focus();
}

function appendToScreen(html) {
    const div = document.createElement('div');
    div.innerHTML = html;
    historyEl.appendChild(div);
    screenEl.scrollTop = screenEl.scrollHeight;
}

function clearTerminalScreen() {
    historyEl.innerHTML = '';
}

function resetRouterToDefaults() {
    routerState.hostname = 'Router';
    routerState.mode = 'user';
    routerState.currentInterface = null;
    routerState.interfaces['GigabitEthernet0/0'] = { ip: null, mask: null, status: 'administratively down', protocol: 'down' };
    routerState.interfaces['GigabitEthernet0/1'] = { ip: null, mask: null, status: 'administratively down', protocol: 'down' };
    updatePrompt();
    clearTerminalScreen();
    appendToScreen('<span style="color:#F59E0B;">% System restart completed. Restored factory defaults.</span>');
}

// تنفيذ الأوامر
function executeCiscoCommand(rawCmd) {
    const cmd = rawCmd.trim();
    if (!cmd) return;

    // توثيق في السجل
    routerState.commandHistory.push(cmd);
    routerState.historyIndex = routerState.commandHistory.length;

    // طباعة السطر المدخل
    appendToScreen(`<span style="color:#34D399; font-weight:bold;">${promptEl.textContent}</span><span style="color:#FFFFFF;">${escapeHtml(cmd)}</span>`);

    const parts = cmd.split(/\s+/);
    const mainCmd = parts[0].toLowerCase();
    const subCmd = parts[1] ? parts[1].toLowerCase() : '';

    // مساعدة
    if (mainCmd === '?' || mainCmd === 'help') {
        showHelpCommands();
        return;
    }

    // التنقل العام
    if (mainCmd === 'exit' || mainCmd === 'quit') {
        if (routerState.mode === 'config-if') {
            routerState.mode = 'config';
            routerState.currentInterface = null;
        } else if (routerState.mode === 'config') {
            routerState.mode = 'privileged';
        } else if (routerState.mode === 'privileged') {
            routerState.mode = 'user';
        }
        updatePrompt();
        return;
    }

    if (mainCmd === 'end' && (routerState.mode === 'config' || routerState.mode === 'config-if')) {
        routerState.mode = 'privileged';
        routerState.currentInterface = null;
        updatePrompt();
        return;
    }

    // نمط المستخدم (User EXEC)
    if (routerState.mode === 'user') {
        if (mainCmd === 'enable' || mainCmd === 'en') {
            routerState.mode = 'privileged';
            updatePrompt();
            return;
        } else if (mainCmd === 'ping') {
            handlePing(parts[1]);
            return;
        } else {
            appendToScreen(`<span style="color:#F87171;">% Type "enable" to enter privileged mode or "?" for available commands.</span>`);
            return;
        }
    }

    // نمط الامتيازات (Privileged EXEC)
    if (routerState.mode === 'privileged') {
        if (mainCmd === 'disable') {
            routerState.mode = 'user';
            updatePrompt();
            return;
        } else if (mainCmd === 'configure' || mainCmd === 'conf') {
            if (subCmd === 'terminal' || subCmd === 't' || !subCmd) {
                routerState.mode = 'config';
                updatePrompt();
                appendToScreen('Enter configuration commands, one per line.  End with CNTL/Z.');
                return;
            }
        } else if (mainCmd === 'show' || mainCmd === 'sh') {
            handleShowCommands(parts.slice(1));
            return;
        } else if (mainCmd === 'ping') {
            handlePing(parts[1]);
            return;
        }
    }

    // نمط الإعداد العام (Global Config)
    if (routerState.mode === 'config') {
        if (mainCmd === 'hostname') {
            if (parts[1]) {
                routerState.hostname = parts[1];
                updatePrompt();
                checkCurrentLabObjective();
                return;
            } else {
                appendToScreen('<span style="color:#F87171;">% Incomplete command. Syntax: hostname [name]</span>');
                return;
            }
        } else if (mainCmd === 'interface' || mainCmd === 'int') {
            const intName = resolveInterfaceName(parts[1]);
            if (intName) {
                routerState.mode = 'config-if';
                routerState.currentInterface = intName;
                updatePrompt();
                return;
            } else {
                appendToScreen('<span style="color:#F87171;">% Invalid interface. Available: GigabitEthernet0/0, GigabitEthernet0/1</span>');
                return;
            }
        } else if (mainCmd === 'ip' && subCmd === 'route') {
            if (parts[2] && parts[3] && parts[4]) {
                routerState.routes.push({ dest: `${parts[2]}/${parts[3]}`, nextHop: parts[4], type: 'Static' });
                appendToScreen('<span style="color:#34D399;">% Static route added to IP routing table.</span>');
                checkCurrentLabObjective();
                return;
            } else {
                appendToScreen('<span style="color:#F87171;">% Incomplete command. Syntax: ip route [prefix] [mask] [next-hop]</span>');
                return;
            }
        } else if (mainCmd === 'do') {
            // تنفيذ أوامر show من داخل config
            const doCmds = parts.slice(1);
            if (doCmds[0] === 'show' || doCmds[0] === 'sh') {
                handleShowCommands(doCmds.slice(1));
                return;
            }
        }
    }

    // نمط المنفذ (Interface Config)
    if (routerState.mode === 'config-if') {
        const intName = routerState.currentInterface;
        if (mainCmd === 'ip' && subCmd === 'address') {
            if (parts[2] && parts[3]) {
                routerState.interfaces[intName].ip = parts[2];
                routerState.interfaces[intName].mask = parts[3];
                appendToScreen(`<span style="color:#34D399;">% IP address ${parts[2]} configured on ${intName}.</span>`);
                checkCurrentLabObjective();
                return;
            } else {
                appendToScreen('<span style="color:#F87171;">% Incomplete command. Syntax: ip address [A.B.C.D] [A.B.C.D]</span>');
                return;
            }
        } else if (mainCmd === 'no' && subCmd === 'shutdown') {
            routerState.interfaces[intName].status = 'up';
            routerState.interfaces[intName].protocol = 'up';
            appendToScreen(`<span style="color:#34D399;">%LINK-5-CHANGED: Interface ${intName}, changed state to up<br>%LINEPROTO-5-UPDOWN: Line protocol on Interface ${intName}, changed state to up</span>`);
            checkCurrentLabObjective();
            return;
        } else if (mainCmd === 'shutdown') {
            routerState.interfaces[intName].status = 'administratively down';
            routerState.interfaces[intName].protocol = 'down';
            appendToScreen(`<span style="color:#F59E0B;">%LINK-5-CHANGED: Interface ${intName}, changed state to administratively down</span>`);
            return;
        }
    }

    // أمر غير معروف
    appendToScreen(`<span style="color:#F87171;">% Invalid input detected at '^' marker. Type "?" for help.</span>`);
}

function resolveInterfaceName(name) {
    if (!name) return null;
    const n = name.toLowerCase();
    if (n === 'g0/0' || n === 'gi0/0' || n.includes('gigabitethernet0/0')) return 'GigabitEthernet0/0';
    if (n === 'g0/1' || n === 'gi0/1' || n.includes('gigabitethernet0/1')) return 'GigabitEthernet0/1';
    return null;
}

function handleShowCommands(args) {
    const sub = args.join(' ').toLowerCase();
    if (sub.includes('ip int') || sub.includes('ip interface brief')) {
        let out = 'Interface              IP-Address      OK? Method Status                Protocol\n';
        for (const [intName, data] of Object.entries(routerState.interfaces)) {
            const ipStr = (data.ip || 'unassigned').padEnd(16);
            const statusStr = (data.status).padEnd(22);
            out += `${intName.padEnd(23)}${ipStr}YES manual ${statusStr}${data.protocol}\n`;
        }
        appendToScreen(`<pre style="margin:0; color:#38BDF8;">${escapeHtml(out)}</pre>`);
    } else if (sub.includes('ip route')) {
        let out = `Codes: L - local, C - connected, S - static, R - RIP, M - mobile, B - BGP\n       D - EIGRP, EX - EIGRP external, O - OSPF, IA - OSPF inter area\n\nGateway of last resort is not set\n\n`;
        for (const r of routerState.routes) {
            out += `${r.type === 'Local' ? 'L' : 'S'}        ${r.dest} [1/0] via ${r.nextHop}\n`;
        }
        for (const [intName, data] of Object.entries(routerState.interfaces)) {
            if (data.status === 'up' && data.ip) {
                out += `C        ${data.ip}/24 is directly connected, ${intName}\n`;
            }
        }
        appendToScreen(`<pre style="margin:0; color:#38BDF8;">${escapeHtml(out)}</pre>`);
    } else if (sub.includes('run') || sub.includes('running-config')) {
        let out = `Building configuration...\n\nCurrent configuration : 1248 bytes\n!\nversion 15.7\nhostname ${routerState.hostname}\n!\n`;
        for (const [intName, data] of Object.entries(routerState.interfaces)) {
            out += `interface ${intName}\n`;
            if (data.ip) out += ` ip address ${data.ip} ${data.mask}\n`;
            else out += ` no ip address\n`;
            if (data.status !== 'up') out += ` shutdown\n`;
            out += `!\n`;
        }
        out += `end\n`;
        appendToScreen(`<pre style="margin:0; color:#38BDF8;">${escapeHtml(out)}</pre>`);
    } else {
        appendToScreen('<span style="color:#F87171;">% Unknown show command. Supported: show ip interface brief, show ip route, show running-config</span>');
    }
}

function handlePing(target) {
    if (!target) {
        appendToScreen('<span style="color:#F87171;">% Incomplete command. Syntax: ping [target-ip]</span>');
        return;
    }
    appendToScreen(`Type escape sequence to abort.<br>Sending 5, 100-byte ICMP Echos to ${escapeHtml(target)}, timeout is 2 seconds:<br><span style="color:#34D399; font-weight:bold;">!!!!!</span><br>Success rate is 100 percent (5/5), round-trip min/avg/max = 1/2/4 ms`);
}

function showHelpCommands() {
    let help = '';
    if (routerState.mode === 'user') {
        help = 'Available commands:\n  enable              Turn on privileged commands\n  ping [target]       Send ICMP Echo request\n  help / ?            Description of the interactive help system';
    } else if (routerState.mode === 'privileged') {
        help = 'Available commands:\n  configure terminal  Enter configuration mode\n  disable             Exit privileged mode\n  ping [target]       Send ICMP Echo request\n  show ip int brief   Display interface status and IP addresses\n  show ip route       Display IP routing table\n  show running-config Display active operating configuration';
    } else if (routerState.mode === 'config') {
        help = 'Available commands:\n  hostname [name]     Set system network name\n  interface [id]      Select an interface to configure (e.g. g0/0)\n  ip route [p] [m] [n] Establish static routes\n  exit / end          Exit configuration mode';
    } else if (routerState.mode === 'config-if') {
        help = 'Available commands:\n  ip address [ip] [m] Set IP address on interface\n  no shutdown         Enable interface\n  shutdown            Administratively disable interface\n  exit                Exit to global configuration mode';
    }
    appendToScreen(`<pre style="margin:0; color:#94A3B8;">${help}</pre>`);
}

function escapeHtml(text) {
    const p = document.createElement('p');
    p.textContent = text;
    return p.innerHTML;
}

// تبديل سيناريوهات المعامل
function loadLabScenario(id) {
    routerState.currentLab = id;
    document.querySelectorAll('.calc-metric-box').forEach(el => {
        el.style.border = '1px solid var(--border-main)';
        el.style.background = 'transparent';
    });
    const activeCard = document.getElementById(`card-lab-${id}`);
    if (activeCard) {
        activeCard.style.border = '1.5px solid var(--brand-primary)';
        activeCard.style.background = 'rgba(59, 130, 246, 0.1)';
    }

    const descEl = document.getElementById('lab-task-desc');
    const badgeEl = document.getElementById('lab-badge-status');
    badgeEl.className = 'badge badge-warning';
    badgeEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> قيد الإنجاز';

    if (id === 1) {
        descEl.innerHTML = 'المهمة الحالية: ادخل إلى وضع الإعداد العام وغير اسم الراوتر إلى <strong>Core-R1</strong> عبر أمر <code>hostname Core-R1</code>.';
    } else if (id === 2) {
        descEl.innerHTML = 'المهمة الحالية: ادخل لمنفذ <strong>g0/0</strong> واضبط العنوان <code>ip address 192.168.10.1 255.255.255.0</code> ثم نفذ <code>no shutdown</code>.';
    } else if (id === 3) {
        descEl.innerHTML = 'المهمة الحالية: أضف مساراً افتراضياً عبر <code>ip route 0.0.0.0 0.0.0.0 192.168.10.254</code> في وضع الإعداد العام.';
    }
    focusCliInput();
}

function checkCurrentLabObjective() {
    const badgeEl = document.getElementById('lab-badge-status');
    const id = routerState.currentLab;
    let completed = false;

    if (id === 1 && routerState.hostname.toLowerCase() === 'core-r1') {
        completed = true;
    } else if (id === 2) {
        const g0 = routerState.interfaces['GigabitEthernet0/0'];
        if (g0.ip === '192.168.10.1' && g0.status === 'up') {
            completed = true;
        }
    } else if (id === 3) {
        const hasRoute = routerState.routes.some(r => r.dest === '0.0.0.0/0.0.0.0' && r.nextHop === '192.168.10.254');
        if (hasRoute) completed = true;
    }

    if (completed) {
        badgeEl.className = 'badge badge-success';
        badgeEl.innerHTML = '<i class="fa-solid fa-circle-check"></i> تم إنجاز المهمة بنجاح!';
        appendToScreen('<span style="color:#10B981; font-weight:bold;">*** [LAB COMPLETED] أحسنت! تم التحقق من نجاح إعدادات المهمة المعملية بنجاح. ***</span>');
    }
}

// معالجة مفاتيح الإدخال
if (inputEl) {
    inputEl.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            executeCiscoCommand(this.value);
            this.value = '';
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (routerState.historyIndex > 0) {
                routerState.historyIndex--;
                this.value = routerState.commandHistory[routerState.historyIndex] || '';
            }
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (routerState.historyIndex < routerState.commandHistory.length - 1) {
                routerState.historyIndex++;
                this.value = routerState.commandHistory[routerState.historyIndex] || '';
            } else {
                routerState.historyIndex = routerState.commandHistory.length;
                this.value = '';
            }
        } else if (e.key === 'Tab') {
            e.preventDefault();
            // إكمال تلقائي أساسي
            const val = this.value.trim().toLowerCase();
            const completions = ['enable', 'configure terminal', 'interface gigabitethernet0/0', 'show ip interface brief', 'show ip route', 'show running-config', 'no shutdown', 'hostname Core-R1'];
            const match = completions.find(c => c.startsWith(val));
            if (match) this.value = match;
        }
    });

    focusCliInput();
}
</script>

<?php require_once '../includes/footer.php'; ?>
