<?php
require "config/db.php";

// حذف القديم إن وجد
$pdo->exec("DELETE FROM level_resources");
echo "Cleared old resources.\n";

$resources = [

// =============================================================
// المستوى 1: أساسيات الشبكات + OSI + TCP/IP
// =============================================================

// ---- فيديوهات عربية ----
[1, "كورس CCNA بالعربي - الحلقة 1: مقدمة في الشبكات ونموذج OSI", "شرح شامل ومبسط لأساسيات الشبكات ونموذج OSI من الصفر بالعربي، مناسب للمبتدئين تماماً.", "video", "ar", "https://www.youtube.com/embed/dh6VKxGABrI", 0, "45 دقيقة", "https://img.youtube.com/vi/dh6VKxGABrI/hqdefault.jpg", "Engineer Mostafa Ibrahim", 1, 1],
[1, "شرح TCP/IP و OSI Model بالعربي | الفرق والمقارنة", "مقارنة تفصيلية بين نموذجَي OSI و TCP/IP مع أمثلة عملية وتطبيقات حقيقية على الشبكات.", "video", "ar", "https://www.youtube.com/embed/3b_ryowsKey", 0, "38 دقيقة", "https://img.youtube.com/vi/3b_ryowsKey/hqdefault.jpg", "Tech With Tim AR", 2, 1],
[1, "شرح كامل لطبقات OSI السبع بالعربي مع أمثلة", "شرح تفصيلي لكل طبقة من طبقات نموذج OSI السبع مع أمثلة حقيقية وتطبيقات عملية.", "video", "ar", "https://www.youtube.com/embed/vv4y_uOneC0", 0, "52 دقيقة", "https://img.youtube.com/vi/vv4y_uOneC0/hqdefault.jpg", "Eng. Salah Mahran", 3, 1],
[1, "بروتوكول TCP مقابل UDP - الفرق والاستخدامات", "شرح الفرق بين TCP و UDP وأمثلة عملية على متى تستخدم كل منهما.", "video", "ar", "https://www.youtube.com/embed/jE_FcgpQ7Co", 0, "25 دقيقة", "https://img.youtube.com/vi/jE_FcgpQ7Co/hqdefault.jpg", "شبكات بالعربي", 4, 0],

// ---- فيديوهات إنجليزية معتمدة ----
[1, "Computer Networking Full Course - OSI Model Deep Dive (EN)", "Complete beginner-friendly course covering all 7 OSI layers with real-world examples and Cisco labs.", "video", "en", "https://www.youtube.com/embed/IPvYjXCsTg8", 0, "1 ساعة 10 دقائق", "https://img.youtube.com/vi/IPvYjXCsTg8/hqdefault.jpg", "NetworkChuck", 5, 1],
[1, "Jeremy's IT Lab - CCNA 200-301 Complete Course Day 1", "The most popular free CCNA course on YouTube. Network fundamentals, OSI, TCP/IP covered systematically.", "video", "en", "https://www.youtube.com/embed/H8W9oMZIVw8", 0, "58 دقيقة", "https://img.youtube.com/vi/H8W9oMZIVw8/hqdefault.jpg", "Jeremy's IT Lab", 6, 1],
[1, "The OSI Model Explained - What Is the OSI Model?", "Crystal clear explanation of the OSI model with animations. Best intro video for networking beginners.", "video", "en", "https://www.youtube.com/embed/Ilk7UXzV_Qc", 0, "20 دقيقة", "https://img.youtube.com/vi/Ilk7UXzV_Qc/hqdefault.jpg", "Cisco Learning & Certifications", 7, 0],

// ---- PDF وموارد مكتوبة ----
[1, "CCNA 200-301 Official Cert Guide - Chapter 1 (Free Preview)", "الفصل الأول المجاني من الكتاب الرسمي لشهادة CCNA يشرح أساسيات الشبكات ونماذج الاتصال.", "pdf", "en", "https://www.ciscopress.com/articles/article.asp?p=2999385", 1, "45 صفحة", null, "Wendell Odom - Cisco Press", 8, 0],
[1, "Cisco Networking Academy - Introduction to Networks", "المنهج الرسمي لأكاديمية سيسكو - مقدمة في الشبكات. يمكن الوصول مجاناً.", "article", "en", "https://www.netacad.com/courses/networking/ccna-introduction-networks", 1, "8 وحدات", null, "Cisco NetAcad", 9, 1],
[1, "Professor Messer's CompTIA Network+ Study Guide (Free PDF)", "دليل مجاني لدراسة أساسيات الشبكات يغطي جميع المفاهيم الأساسية المطلوبة.", "pdf", "en", "https://www.professormesser.com/n10-008-study-group/", 1, "200 صفحة", null, "Professor Messer", 10, 0],

// ---- تفاعلي ----
[1, "Cisco Packet Tracer - تطبيق عملي على OSI", "حمّل Packet Tracer ومارس إنشاء شبكات بسيطة وتتبع الحزم عبر طبقات OSI.", "interactive", "mixed", "https://www.netacad.com/cisco-packet-tracer", 1, "تطبيق مجاني", null, "Cisco Systems", 11, 1],
[1, "Subnet Calculator & Network Tools", "أداة تفاعلية لحساب الشبكات الفرعية وتصور نموذج OSI في الوقت الفعلي.", "interactive", "en", "https://www.subnet-calculator.com/", 1, "أداة مجانية", null, "Online Tools", 12, 0],

// =============================================================
// المستوى 2: IPv4 + Subnetting + VLSM
// =============================================================

[2, "شرح Subnetting بالعربي من الصفر للاحتراف", "الشرح الأشمل لـ Subnetting بالعربي - من المفاهيم الأساسية حتى VLSM والحسابات المتقدمة.", "video", "ar", "https://www.youtube.com/embed/ecCuyq-Wprc", 0, "1 ساعة 20 دقيقة", "https://img.youtube.com/vi/ecCuyq-Wprc/hqdefault.jpg", "Engineer Mohamed Hassan", 1, 1],
[2, "تقسيم الشبكات IPv4 - الحصة الكاملة", "حصة كاملة تشرح عنونة IPv4 والـ CIDR والـ Subnetting بطريقة عملية خطوة بخطوة.", "video", "ar", "https://www.youtube.com/embed/BWZ-MkB4err", 0, "55 دقيقة", "https://img.youtube.com/vi/BWZ-MkB4err/hqdefault.jpg", "شبكات عربية", 2, 1],
[2, "Subnetting Mastery - Practical Networking (EN)", "The best English subnetting course. Learn to subnet any network in your head within seconds.", "video", "en", "https://www.youtube.com/embed/BWZ-MkB4err", 0, "7 حلقات × 15 دقيقة", "https://img.youtube.com/vi/BWZ-MkB4err/hqdefault.jpg", "Practical Networking", 3, 1],
[2, "Subnetting Questions CCNA - 50 Practice Problems", "50 مسألة تدريبية على Subnetting بمستويات متدرجة مع الشرح والحل التفصيلي.", "video", "en", "https://www.youtube.com/embed/ZxAwQB8TZsM", 0, "2 ساعة", "https://img.youtube.com/vi/ZxAwQB8TZsM/hqdefault.jpg", "Jeremy's IT Lab", 4, 1],
[2, "IPv6 Addressing - الانتقال من IPv4 إلى IPv6", "شرح بنية IPv6 والفرق عن IPv4 وكيفية التحول ومناطق التطبيق.", "video", "ar", "https://www.youtube.com/embed/irhS0ASkvy8", 0, "40 دقيقة", "https://img.youtube.com/vi/irhS0ASkvy8/hqdefault.jpg", "Eng. Tarek Networks", 5, 0],
[2, "Subnet Practice Tool - SubnettingPractice.com", "موقع تفاعلي لممارسة حسابات الـ Subnetting مع تصحيح فوري وإحصائيات تقدم.", "interactive", "en", "https://subnettingpractice.com/", 1, "تمارين غير محدودة", null, "SubnettingPractice.com", 6, 1],
[2, "VLSM Calculator - حاسبة VLSM التفاعلية", "أداة لحساب VLSM تلقائياً وعرض جداول تفصيلية للشبكات الفرعية.", "interactive", "en", "https://www.vlsm-calc.net/", 1, "أداة مجانية", null, "VLSM-Calc.net", 7, 0],
[2, "Cisco CCNA Subnetting Cheat Sheet (PDF)", "ورقة مرجعية للـ Subnetting تحتوي جداول العناوين والقواعد السريعة.", "pdf", "en", "https://www.pcwdld.com/subnetting-cheat-sheet-subnet-mask", 1, "2 صفحات", null, "PCWDLD Reference", 8, 0],
[2, "DHCP Configuration Lab - Packet Tracer", "مختبر عملي في Packet Tracer لإعداد DHCP Server وتوزيع العناوين التلقائية.", "interactive", "mixed", "https://www.netacad.com/courses/packet-tracer", 1, "45 دقيقة", null, "Cisco NetAcad", 9, 1],

// =============================================================
// المستوى 3: Routing Protocols + Switching + VLANs
// =============================================================

[3, "شرح OSPF بالعربي من الصفر للاحتراف", "الشرح الشامل لبروتوكول OSPF - المفاهيم والإعداد والتكوين على أجهزة Cisco.", "video", "ar", "https://www.youtube.com/embed/kfvJ8QVJscc", 0, "1 ساعة 30 دقيقة", "https://img.youtube.com/vi/kfvJ8QVJscc/hqdefault.jpg", "Network Academy AR", 1, 1],
[3, "شرح VLANs والـ Trunking بالعربي", "شرح تفصيلي لـ VLANs وإعداد Trunk Ports والفرق بين Access و Trunk على محولات Cisco.", "video", "ar", "https://www.youtube.com/embed/MmwF1oHOvmg", 0, "55 دقيقة", "https://img.youtube.com/vi/MmwF1oHOvmg/hqdefault.jpg", "Cisco AR Academy", 2, 1],
[3, "EIGRP vs OSPF vs RIP - المقارنة الشاملة", "مقارنة تفصيلية بين بروتوكولات التوجيه الثلاثة مع أمثلة عملية ومتى تستخدم كل منها.", "video", "ar", "https://www.youtube.com/embed/9ekf4Frv1eo", 0, "45 دقيقة", "https://img.youtube.com/vi/9ekf4Frv1eo/hqdefault.jpg", "Tech Experts AR", 3, 1],
[3, "Spanning Tree Protocol STP - الشرح الكامل", "شرح STP وكيف يمنع الحلقات في شبكات Ethernet مع إعداد Root Bridge.", "video", "en", "https://www.youtube.com/embed/japdEY1UKe4", 0, "35 دقيقة", "https://img.youtube.com/vi/japdEY1UKe4/hqdefault.jpg", "Jeremy's IT Lab", 4, 1],
[3, "OSPF Configuration Lab - Packet Tracer", "مختبر عملي لإعداد OSPF على شبكة متعددة الأجهزة في Packet Tracer.", "interactive", "mixed", "https://www.netacad.com/courses/packet-tracer", 1, "1 ساعة", null, "Cisco NetAcad", 5, 1],
[3, "Inter-VLAN Routing - Router on a Stick", "شرح وتطبيق عملي لـ Inter-VLAN Routing باستخدام تقنية Router-on-a-Stick.", "video", "en", "https://www.youtube.com/embed/cEL6H1sHcrg", 0, "28 دقيقة", "https://img.youtube.com/vi/cEL6H1sHcrg/hqdefault.jpg", "NetworkChuck", 6, 0],
[3, "GNS3 Network Simulator - تثبيت وأول مختبر", "كيفية تثبيت GNS3 وإنشاء أول مختبر افتراضي مع بروتوكولات التوجيه.", "video", "ar", "https://www.youtube.com/embed/Bes5sYaOSBo", 0, "50 دقيقة", "https://img.youtube.com/vi/Bes5sYaOSBo/hqdefault.jpg", "GNS3 AR Tutorial", 7, 0],
[3, "Routing & Switching Study Guide PDF", "دليل شامل لدراسة بروتوكولات التوجيه والتبديل بالأمثلة والمخططات.", "pdf", "en", "https://ptgmedia.pearsoncmg.com/images/9780138213176/samplepages/9780138213176_ch02.pdf", 1, "85 صفحة", null, "Cisco Press", 8, 0],

// =============================================================
// المستوى 4: Network Security + Firewalls + Advanced
// =============================================================

[4, "شرح Network Security بالعربي - المفاهيم والحلول", "شرح شامل لأمن الشبكات - أنواع الهجمات والدفاعات والـ Firewalls وVPN.", "video", "ar", "https://www.youtube.com/embed/sdpxddDzXfE", 0, "1 ساعة 15 دقيقة", "https://img.youtube.com/vi/sdpxddDzXfE/hqdefault.jpg", "CyberSec AR", 1, 1],
[4, "Cisco ASA Firewall Configuration - الشرح الكامل", "كيفية إعداد وتكوين Cisco ASA Firewall من الصفر مع أمثلة عملية.", "video", "ar", "https://www.youtube.com/embed/gKLkjp1XhcU", 0, "1 ساعة", "https://img.youtube.com/vi/gKLkjp1XhcU/hqdefault.jpg", "Security Networks AR", 2, 1],
[4, "VPN Configuration - Site-to-Site & Remote Access", "إعداد VPN من نوعَي Site-to-Site وRemote Access على أجهزة Cisco.", "video", "en", "https://www.youtube.com/embed/67kItGjvSs4", 0, "48 دقيقة", "https://img.youtube.com/vi/67kItGjvSs4/hqdefault.jpg", "NetworkChuck", 3, 1],
[4, "Ethical Hacking & Network Penetration Testing", "مقدمة في اختبار الاختراق وكيف يُستخدم لاكتشاف ثغرات الشبكات.", "video", "en", "https://www.youtube.com/embed/3Kq1MIfTWCE", 0, "2 ساعة", "https://img.youtube.com/vi/3Kq1MIfTWCE/hqdefault.jpg", "TCM Security", 4, 1],
[4, "Wireshark Network Analysis - Packet Capture Deep Dive", "تحليل حزم الشبكة باستخدام Wireshark واكتشاف الهجمات الشائعة.", "video", "en", "https://www.youtube.com/embed/lb1Dw0elw0Q", 0, "55 دقيقة", "https://img.youtube.com/vi/lb1Dw0elw0Q/hqdefault.jpg", "David Bombal", 5, 1],
[4, "ACL (Access Control Lists) - التحكم في حركة المرور", "شرح Standard و Extended ACLs وكيفية تطبيقها لتأمين الشبكات على Cisco.", "video", "ar", "https://www.youtube.com/embed/9mNm0y7h6cE", 0, "40 دقيقة", "https://img.youtube.com/vi/9mNm0y7h6cE/hqdefault.jpg", "Cisco AR Security", 6, 1],
[4, "Network Security Checklist - SANS Institute (PDF)", "قائمة فحص أمن الشبكات من معهد SANS المتخصص في الأمن السيبراني.", "pdf", "en", "https://www.sans.org/media/score/checklists/network-perimeter-checklist.pdf", 1, "12 صفحة", null, "SANS Institute", 7, 0],
[4, "TryHackMe - Network Fundamentals & Security Room", "منصة تفاعلية لممارسة أمن الشبكات بشكل عملي في بيئة آمنة.", "interactive", "en", "https://tryhackme.com/path/outline/jr-penetration-tester", 1, "40 ساعة تدريب", null, "TryHackMe", 8, 1],
[4, "Cisco CyberOps Associate - Free Course", "كورس مجاني من سيسكو في عمليات الأمن السيبراني.", "article", "en", "https://www.netacad.com/courses/cybersecurity/cyberops-associate", 1, "70 ساعة", null, "Cisco NetAcad", 9, 1],

];

$stmt = $pdo->prepare("INSERT INTO level_resources (level_id,title,description,resource_type,resource_lang,url,is_external,duration_label,thumbnail_url,author_name,sort_order,is_featured) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");

$count = 0;
foreach ($resources as $r) {
    $stmt->execute($r);
    $count++;
}
echo "Inserted {$count} resources successfully!\n";

// عرض ملخص
$summary = $pdo->query("SELECT l.level_name, COUNT(lr.id) as cnt FROM levels l LEFT JOIN level_resources lr ON lr.level_id=l.id GROUP BY l.id, l.level_name ORDER BY l.level_order")->fetchAll();
foreach ($summary as $s) {
    echo "  -> ".$s["level_name"].": ".$s["cnt"]." resources\n";
}
