/**
 * Comprehensive IPv4 Subnet Simulator & Educational Engine - NetLearn Pro
 * محرك التحليل الرياضي والتجزئة والتحديات المعمارية لشبكات IPv4
 */

function ipToLong(ip) {
    if (!ip) return null;
    const parts = ip.trim().split('.');
    if (parts.length !== 4) return null;
    for (let i = 0; i < 4; i++) {
        const n = Number(parts[i]);
        if (isNaN(n) || n < 0 || n > 255 || parts[i].trim() === '' || (parts[i].length > 1 && parts[i].startsWith('0'))) {
            return null;
        }
    }
    return ((Number(parts[0]) << 24) | (Number(parts[1]) << 16) | (Number(parts[2]) << 8) | Number(parts[3])) >>> 0;
}

function longToIp(long) {
    return [
        (long >>> 24) & 255,
        (long >>> 16) & 255,
        (long >>> 8) & 255,
        long & 255
    ].join('.');
}

function calculateSubnet(ipStr, cidr) {
    cidr = parseInt(cidr, 10);
    const ipLong = ipToLong(ipStr);
    if (ipLong === null || isNaN(cidr) || cidr < 0 || cidr > 32) {
        return null;
    }

    // حساب قناع الشبكة والـ Wildcard
    const maskLong = cidr === 0 ? 0 : ((0xFFFFFFFF << (32 - cidr)) >>> 0);
    const wildcardLong = (~maskLong) >>> 0;
    
    // عنوان الشبكة وعنوان البث
    const networkLong = (ipLong & maskLong) >>> 0;
    const broadcastLong = (networkLong | wildcardLong) >>> 0;
    
    // حساب الأجهزة مع الحالات الخاصة المعيارية
    const totalHosts = Math.pow(2, 32 - cidr);
    let usableHosts = 0;
    let firstHostLong = networkLong;
    let lastHostLong = broadcastLong;
    let note = '';

    if (cidr === 32) {
        usableHosts = 1;
        firstHostLong = networkLong;
        lastHostLong = networkLong;
        note = 'مسار جهاز منفرد (Host Route / Loopback Interface).';
    } else if (cidr === 31) {
        usableHosts = 2; // RFC 3021 Point-to-Point Links
        firstHostLong = networkLong;
        lastHostLong = broadcastLong;
        note = 'معيار RFC 3021: رابط نقطة لنقطة بدون هدر عنوان للشبكة أو البث.';
    } else if (cidr === 0) {
        usableHosts = totalHosts - 2;
        firstHostLong = (networkLong + 1) >>> 0;
        lastHostLong = (broadcastLong - 1) >>> 0;
        note = 'المسار الافتراضي لجميع عناوين الإنترنت (0.0.0.0/0).';
    } else {
        usableHosts = Math.max(0, totalHosts - 2);
        firstHostLong = (networkLong + 1) >>> 0;
        lastHostLong = (broadcastLong - 1) >>> 0;
    }

    // فئة الـ IP ونوعه (خاص أم عام)
    const firstOctet = (ipLong >>> 24) & 255;
    let ipClass = 'Unknown';
    if (firstOctet >= 1 && firstOctet <= 126) ipClass = 'Class A';
    else if (firstOctet === 127) ipClass = 'Loopback Range';
    else if (firstOctet >= 128 && firstOctet <= 191) ipClass = 'Class B';
    else if (firstOctet >= 192 && firstOctet <= 223) ipClass = 'Class C';
    else if (firstOctet >= 224 && firstOctet <= 239) ipClass = 'Class D (Multicast)';
    else if (firstOctet >= 240) ipClass = 'Class E (Experimental)';

    let ipType = 'عنوان عام (Public IP)';
    if (firstOctet === 10 || 
       (firstOctet === 172 && (ipLong >>> 16 & 255) >= 16 && (ipLong >>> 16 & 255) <= 31) ||
       (firstOctet === 192 && (ipLong >>> 16 & 255) === 168)) {
        ipType = 'عنوان خاص محلي (Private RFC 1918)';
    } else if (firstOctet === 127) {
        ipType = 'عنوان محلي للاختبار (Loopback)';
    } else if (firstOctet === 169 && (ipLong >>> 16 & 255) === 254) {
        ipType = 'عنوان تلقائي (APIPA / Link-Local)';
    }

    return {
        ip: ipStr,
        cidr: cidr,
        mask: longToIp(maskLong),
        wildcard: longToIp(wildcardLong),
        network: longToIp(networkLong),
        broadcast: longToIp(broadcastLong),
        firstHost: usableHosts > 0 ? longToIp(firstHostLong) : 'غير متاح',
        lastHost: usableHosts > 0 ? longToIp(lastHostLong) : 'غير متاح',
        totalHosts: totalHosts.toLocaleString(),
        usableHosts: usableHosts.toLocaleString(),
        ipClass: ipClass,
        ipType: ipType,
        maskLong: maskLong,
        note: note
    };
}

function updateCalculatorUI() {
    const ipInput = document.getElementById('calc-ip');
    const cidrInput = document.getElementById('calc-cidr');
    const cidrRange = document.getElementById('calc-cidr-range');
    const cidrDisplay = document.getElementById('cidr-display');

    if (!ipInput || !cidrInput) return;

    const ip = ipInput.value.trim();
    const cidr = cidrInput.value;
    if (cidrDisplay) cidrDisplay.textContent = cidr;

    const res = calculateSubnet(ip, cidr);
    const errorEl = document.getElementById('calc-error');
    const noteEl = document.getElementById('calc-note');

    if (!res) {
        if (errorEl) errorEl.style.display = 'flex';
        return;
    }
    if (errorEl) errorEl.style.display = 'none';

    document.getElementById('res-network').textContent = res.network;
    document.getElementById('res-broadcast').textContent = res.broadcast;
    document.getElementById('res-mask').textContent = res.mask;
    document.getElementById('res-wildcard').textContent = res.wildcard;
    document.getElementById('res-range').textContent = res.firstHost + ' - ' + res.lastHost;
    document.getElementById('res-usable').textContent = res.usableHosts;
    document.getElementById('res-total').textContent = res.totalHosts;
    document.getElementById('res-type').textContent = res.ipClass + ' | ' + res.ipType;

    if (noteEl) {
        if (res.note) {
            noteEl.style.display = 'block';
            noteEl.textContent = res.note;
        } else {
            noteEl.style.display = 'none';
        }
    }

    // تحديث التمثيل الثنائي (Binary Bits)
    renderBinaryBits(res.maskLong, res.cidr);

    // تحديث جدول تقسيم الشبكات
    renderSubnetTable(res);
}

function renderBinaryBits(maskLong, cidr) {
    const container = document.getElementById('binary-bits-container');
    if (!container) return;

    let html = '';
    for (let octet = 3; octet >= 0; octet--) {
        html += '<div style="display:flex; gap:2px; margin:0 3px;">';
        for (let bit = 7; bit >= 0; bit--) {
            const bitIndex = (3 - octet) * 8 + (7 - bit);
            const isNet = bitIndex < cidr;
            html += `<span class="bit-chip ${isNet ? 'net' : 'host'}" title="${isNet ? 'بت شبكة (1)' : 'بت مضيف (0)'}">${isNet ? '1' : '0'}</span>`;
        }
        html += '</div>';
        if (octet > 0) {
            html += '<span style="color:#94a3b8; font-size:18px; align-self:center; font-weight:bold;">.</span>';
        }
    }
    container.innerHTML = html;
}

function renderSubnetTable(res) {
    const container = document.getElementById('subnet-division-container');
    if (!container) return;

    // نقوم بتوليد الشبكات الفرعية إذا كان الـ CIDR بين 24 و 30 كمثال تطبيقي مريح
    const cidr = res.cidr;
    if (cidr < 24 || cidr > 30) {
        container.innerHTML = `<div style="text-align:center; padding:15px; color:var(--text-muted); font-size:0.88rem;">
            جدول التجزئة التفصيلي متاح للمعاينة المباشرة بين الأقنعة /24 و /30 (حجم القفزة مناسب للمعاينة الأكاديمية السريعة).
        </div>`;
        return;
    }

    const ipParts = res.ip.split('.');
    const basePrefix = `${ipParts[0]}.${ipParts[1]}.${ipParts[2]}`;
    const step = Math.pow(2, 32 - cidr);
    const subnetsCount = Math.min(8, 256 / step); // إظهار حتى 8 شبكات أولى

    let rows = '';
    for (let i = 0; i < subnetsCount; i++) {
        const netStart = i * step;
        const netEnd = netStart + step - 1;
        const firstU = (cidr >= 31) ? netStart : netStart + 1;
        const lastU = (cidr >= 31) ? netEnd : netEnd - 1;

        rows += `<tr>
            <td class="code-font" style="font-weight:700; color:var(--primary);">الشبكة #${i + 1}</td>
            <td class="code-font">${basePrefix}.${netStart}</td>
            <td class="code-font" style="color:var(--success-text);">${basePrefix}.${firstU} - ${basePrefix}.${lastU}</td>
            <td class="code-font" style="color:var(--danger-text);">${basePrefix}.${netEnd}</td>
            <td class="code-font">${step - (cidr >= 31 ? 0 : 2)}</td>
        </tr>`;
    }

    container.innerHTML = `
        <table class="academic-table" style="font-size:0.86rem; margin-top:10px;">
            <thead>
                <tr>
                    <th>الشبكة</th>
                    <th>عنوان الشبكة (Network ID)</th>
                    <th>نطاق الأجهزة الصالحة</th>
                    <th>عنوان البث (Broadcast)</th>
                    <th>عدد الأجهزة</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
    `;
}

// Subnet Challenge Generator with Multiple Question Types
let currentChallenge = null;

function generateSubnetChallenge() {
    const cidrs = [24, 25, 26, 27, 28, 29, 30];
    const cidr = cidrs[Math.floor(Math.random() * cidrs.length)];
    const o1 = 192, o2 = 168;
    const o3 = Math.floor(Math.random() * 50) + 1;
    const o4 = Math.floor(Math.random() * 254) + 1;
    const ip = `${o1}.${o2}.${o3}.${o4}`;
    
    const sol = calculateSubnet(ip, cidr);
    const types = ['network', 'broadcast', 'usable_hosts', 'mask'];
    const challengeType = types[Math.floor(Math.random() * types.length)];
    
    let questionText = '';
    let correctAnswer = '';
    let explanation = '';

    if (challengeType === 'network') {
        questionText = `ما هو عنوان الشبكة (Network Address) للعنوان: <code class="code-font">${ip}/${cidr}</code>؟`;
        correctAnswer = sol.network;
        explanation = `حجم القفزة لقناع /${cidr} هو ${256 - (sol.maskLong & 255)}. عند تطبيق AND المنطقية بين العنوان وقناع ${sol.mask} ينتج عنوان الشبكة: ${sol.network}`;
    } else if (challengeType === 'broadcast') {
        questionText = `ما هو عنوان البث العام (Broadcast Address) للعنوان: <code class="code-font">${ip}/${cidr}</code>؟`;
        correctAnswer = sol.broadcast;
        explanation = `عنوان البث لشبكة /${cidr} التي تضم العنوان ${ip} هو آخر عنوان في النطاق الفرعي وهو: ${sol.broadcast}`;
    } else if (challengeType === 'mask') {
        questionText = `ما هو قناع الشبكة (Subnet Mask) للبادئة: <code class="code-font">/${cidr}</code>؟`;
        correctAnswer = sol.mask;
        explanation = `البادئة /${cidr} تعني أن أول ${cidr} بتات تساوي 1، مما يعطي القناع المعياري: ${sol.mask}`;
    } else {
        questionText = `ما هو عدد الأجهزة الصالحة للاستخدام (Usable Hosts) في شبكة بقناع <code class="code-font">/${cidr}</code>؟`;
        correctAnswer = sol.usableHosts.replace(/,/g, '');
        explanation = `القاعدة الرياضية: 2^(32 - ${cidr}) - 2 = 2^${32 - cidr} - 2 = ${sol.usableHosts} جهاز متاح.`;
    }

    currentChallenge = {
        type: challengeType,
        ip: ip,
        cidr: cidr,
        question: questionText,
        correctAnswer: correctAnswer,
        explanation: explanation
    };

    const qBox = document.getElementById('challenge-question');
    const resBox = document.getElementById('challenge-feedback');
    const inputEl = document.getElementById('challenge-user-answer');
    
    if (qBox) qBox.innerHTML = questionText;
    if (resBox) resBox.style.display = 'none';
    if (inputEl) {
        inputEl.value = '';
        inputEl.focus();
    }
}

function verifyChallengeAnswer() {
    if (!currentChallenge) return;
    const inputEl = document.getElementById('challenge-user-answer');
    const userAns = inputEl ? inputEl.value.trim() : '';
    const feedback = document.getElementById('challenge-feedback');
    
    if (!userAns) {
        alert('يرجى كتابة إجابتك أولاً!');
        return;
    }

    feedback.style.display = 'flex';
    const isCorrect = (userAns.toLowerCase() === currentChallenge.correctAnswer.toLowerCase());

    if (isCorrect) {
        feedback.className = 'academic-alert alert-success';
        feedback.innerHTML = `<div><i class="fa-solid fa-circle-check" style="font-size:18px;"></i></div>
                              <div><strong>إجابة صحيحة وممتازة!</strong><br>${currentChallenge.explanation}</div>`;
    } else {
        feedback.className = 'academic-alert alert-danger';
        feedback.innerHTML = `<div><i class="fa-solid fa-circle-xmark" style="font-size:18px;"></i></div>
                              <div><strong>إجابة غير صحيحة!</strong> الإجابة الصحيحة هي: <code class="code-font">${currentChallenge.correctAnswer}</code><br><strong>الشرح الهندسي:</strong> ${currentChallenge.explanation}</div>`;
    }

    // حفظ المحاولة في قاعدة البيانات عبر AJAX
    try {
        fetch('save_lab_attempt.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                exercise_type: currentChallenge.type,
                target_ip: currentChallenge.ip,
                cidr: currentChallenge.cidr,
                user_answer: userAns,
                correct_answer: currentChallenge.correctAnswer,
                is_correct: isCorrect ? 1 : 0
            })
        }).catch(() => {});
    } catch (e) {}
}

// تهيئة التفاعلات فور تحميل الصفحة
document.addEventListener('DOMContentLoaded', () => {
    const ipInput = document.getElementById('calc-ip');
    const cidrInput = document.getElementById('calc-cidr');
    const cidrRange = document.getElementById('calc-cidr-range');

    if (ipInput && cidrInput && cidrRange) {
        ipInput.addEventListener('input', updateCalculatorUI);
        
        cidrInput.addEventListener('input', (e) => {
            let val = parseInt(e.target.value, 10);
            if (isNaN(val)) val = 24;
            if (val < 0) val = 0;
            if (val > 32) val = 32;
            cidrRange.value = val;
            updateCalculatorUI();
        });

        cidrRange.addEventListener('input', (e) => {
            cidrInput.value = e.target.value;
            updateCalculatorUI();
        });

        updateCalculatorUI();
        generateSubnetChallenge();
    }
});
