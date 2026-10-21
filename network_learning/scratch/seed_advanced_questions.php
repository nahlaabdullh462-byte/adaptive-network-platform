<?php
/**
 * Academic Graduation Project Question Bank Seeder
 * Adds 44+ comprehensive, high-caliber Cisco CCNA questions across all 4 levels.
 */
require_once __DIR__ . '/../config/db.php';

$questions = [
    // =========================================================================
    // LEVEL 1: معمارية الشبكات ونموذج OSI (OSI, TCP/IP, Ethernet, Cabling)
    // =========================================================================
    [
        'level_id' => 1,
        'topic_id' => 4,
        'question_text' => 'أي من الحقول التالية يوجد حصرياً في ترويسة (Header) بروتوكول TCP ولا يتواجد مطلقاً في ترويسة بروتوكول UDP؟',
        'option_a' => 'Source Port (منفذ المصدر)',
        'option_b' => 'Destination Port (منفذ الوجهة)',
        'option_c' => 'Sequence Number & Acknowledgment (أرقام التسلسل والتأكيد)',
        'option_d' => 'Checksum (المجموع الاختباري)',
        'correct_option' => 'C',
        'explanation' => 'يتميز بروتوكول TCP بأنه موجه بالاتصال (Connection-Oriented) وموثوق، لذا يحتوي على Sequence Number و Acknowledgment Number لترتيب البيانات وتأكيد استلامها، بينما بروتوكول UDP خفيف وبسيط (Connectionless) ولا يحتوي على هذه الحقول.',
        'difficulty' => 'medium',
        'domain_name' => 'Transport Protocols'
    ],
    [
        'level_id' => 1,
        'topic_id' => 2,
        'question_text' => 'ما هو البروتوكول المسؤول عن اكتشاف ومعرفة عنوان MAC الفيزيائي المقابل لعنوان IPv4 معروف في نفس الشبكة المحلية؟',
        'option_a' => 'DNS (Domain Name System)',
        'option_b' => 'ARP (Address Resolution Protocol)',
        'option_c' => 'DHCP (Dynamic Host Configuration Protocol)',
        'option_d' => 'ICMP (Internet Control Message Protocol)',
        'correct_option' => 'B',
        'explanation' => 'بروتوكول ARP يرسل رسالة بث عام (ARP Request Broadcast) ليسأل عن عنوان MAC الخاص بعنوان IP محدد، ويقوم الجهاز المعني بالرد برسالة أحادية (ARP Reply Unicast).',
        'difficulty' => 'easy',
        'domain_name' => 'Network Hardware'
    ],
    [
        'level_id' => 1,
        'topic_id' => 2,
        'question_text' => 'في شبكة إيثرنت حديثة تعمل بوضع الازدواج الكامل (Full-Duplex) بين جهاز حاسوب ومنفذ مبدل (Switch)، كم عدد مجالات التصادم الناتجة؟',
        'option_a' => 'مجال تصادم واحد مشترك بينهما',
        'option_b' => 'لا توجد أي مجالات تصادم على الإطلاق (Zero Collisions)',
        'option_c' => 'مجالان تصادم منفصلان',
        'option_d' => 'يتغير بحسب سرعة الكابل',
        'correct_option' => 'B',
        'explanation' => 'في وضع الازدواج الكامل (Full-Duplex)، يتم استخدام أزواج أسلاك منفصلة للإرسال (Tx) والاستقبال (Rx)، مما يلغي حدوث أي تصادمات ميكانيكية بين الإشارات ويوفر قناة خالية تماماً من التصادم.',
        'difficulty' => 'medium',
        'domain_name' => 'Ethernet Architecture'
    ],
    [
        'level_id' => 1,
        'topic_id' => 2,
        'question_text' => 'كم عدد مجالات البث العام (Broadcast Domains) التي ينشئها راوتر يمتلك 4 منافذ GigabitEthernet نشطة متصل كل منها بمبدل مختلف؟',
        'option_a' => 'مجال بث واحد لجميع المنافذ',
        'option_b' => 'مجالان للبث فقط',
        'option_c' => '4 مجالات بث مستقلة تماماً',
        'option_d' => '8 مجالات بث',
        'correct_option' => 'C',
        'explanation' => 'الراوتر (Router) يقوم بحجب رسائل البث العام (Broadcasts) بشكل افتراضي، وكل منفذ فيزيائي على الراوتر يمثل شبكة فرعية مستقلة ومجال بث منفصل تماماً (Broadcast Domain).',
        'difficulty' => 'easy',
        'domain_name' => 'Network Hardware'
    ],
    [
        'level_id' => 1,
        'topic_id' => 1,
        'question_text' => 'ما الذي يضاف في ذيل إطار البيانات (Trailer) في طبقة ربط البيانات (Data Link Layer) لاكتشاف الأخطاء التي قد تصيب الإشارة أثناء النقل؟',
        'option_a' => 'IP Header (ترويسة بروتوكول الإنترنت)',
        'option_b' => 'Frame Check Sequence (FCS / CRC)',
        'option_c' => 'Time to Live (TTL)',
        'option_d' => 'SYN Flag',
        'correct_option' => 'B',
        'explanation' => 'تقوم طبقة ربط البيانات بإضافة حقل FCS في ذيل الإطار (Trailer) ويحتوي على ناتج خوارزمية فحص التكرار الدوري (CRC) للتأكد من وصول الإطار سليماً دون تشوه فيزيائي.',
        'difficulty' => 'medium',
        'domain_name' => 'OSI Architecture'
    ],
    [
        'level_id' => 1,
        'topic_id' => 3,
        'question_text' => 'كم عدد البتات (Bits) التي يتكون منها عنوان بروتوكول الإنترنت الإصدار السادس (IPv6)؟',
        'option_a' => '32 بت',
        'option_b' => '64 بت',
        'option_c' => '128 بت',
        'option_d' => '256 بت',
        'correct_option' => 'C',
        'explanation' => 'يتكون عنوان IPv6 من 128 بت مقسمة إلى 8 مجموعات سداسية عشرية (Hexadecimal) تفصل بينها نقطتان، مما يمنح مساحة عنونة هائلة بلغت 2^128 عنواناً للقضاء على نفاد عناوين IPv4 المكونة من 32 بت فقط.',
        'difficulty' => 'easy',
        'domain_name' => 'IPv6 Fundamentals'
    ],
    [
        'level_id' => 1,
        'topic_id' => 3,
        'question_text' => 'ما هو نوع كابل الإيثرنت النحاسي المستخدم قديماً لتوصيل جهازين من نفس النوع مباشرة (مثل حاسوب بحاسوب، أو راوتر براوتر) قبل انتشار ميزة Auto-MDIX؟',
        'option_a' => 'Straight-Through Cable (كابل مستقيم)',
        'option_b' => 'Crossover Cable (كابل متعاكس)',
        'option_c' => 'Rollover Cable (كابل كونسول)',
        'option_d' => 'Coaxial Cable (كابل محوري)',
        'correct_option' => 'B',
        'explanation' => 'الكابل المتعاكس (Crossover Cable) يبدل أزواج الإرسال (Tx) والاستقبال (Rx) عند الطرفين وفق معايير T568A و T568B، لتمكين جهازين يرسلان على نفس الأزواج من تبادل الإشارات.',
        'difficulty' => 'medium',
        'domain_name' => 'Cabling & Physical'
    ],
    [
        'level_id' => 1,
        'topic_id' => 2,
        'question_text' => 'كيف يقوم مبدل الإيثرنت (Switch) ببناء وتحديث جدول عناوين الـ MAC Address الخاص به؟',
        'option_a' => 'من خلال فحص عنوان MAC المصدر (Source MAC) في الإطارات الواردة',
        'option_b' => 'من خلال فحص عنوان MAC الوجهة (Destination MAC)',
        'option_c' => 'من خلال طلب قائمة العناوين من الراوتر الرئيسي',
        'option_d' => 'عبر إدخال يدوي إلزامي من المهندس لكل جهاز',
        'correct_option' => 'A',
        'explanation' => 'عندما تصل إطارة إلى أي منفذ في المبدل، يقرأ المبدل عنوان MAC المصدر (Source MAC) ويربطه تلقائياً برقم المنفذ الذي دخلت منه الإطارة في جدول CAM Table مع مؤقت صلاحية (Aging Timer).',
        'difficulty' => 'medium',
        'domain_name' => 'Network Hardware'
    ],
    [
        'level_id' => 1,
        'topic_id' => 4,
        'question_text' => 'ما هو رقم المنفذ القياسي (Port Number) والبروتوكول المستخدم افتراضياً لمعظم استعلامات خوادم أسماء النطاقات (DNS Lookups)؟',
        'option_a' => 'Port 80 عبر TCP',
        'option_b' => 'Port 53 عبر UDP',
        'option_c' => 'Port 443 عبر TCP',
        'option_d' => 'Port 25 عبر UDP',
        'correct_option' => 'B',
        'explanation' => 'تستخدم استعلامات DNS العادية المنفذ 53 عبر بروتوكول UDP لسرعته وقلة التكلفة الإضافية في الرسائل القصيرة، بينما يستخدم TCP Port 53 في نقل مناطق النطاقات (Zone Transfers) أو الرسائل الأكبر من 512 بايت.',
        'difficulty' => 'easy',
        'domain_name' => 'Transport Protocols'
    ],
    [
        'level_id' => 1,
        'topic_id' => 1,
        'question_text' => 'في نموذج TCP/IP المكون من 4 طبقات، أي طبقة تعادل وظيفياً الطبقات الثلاث العليا (Application, Presentation, Session) من نموذج OSI؟',
        'option_a' => 'Transport Layer (طبقة النقل)',
        'option_b' => 'Application Layer (طبقة التطبيقات)',
        'option_c' => 'Internet Layer (طبقة الإنترنت)',
        'option_d' => 'Network Access Layer (طبقة الوصول للشبكة)',
        'correct_option' => 'B',
        'explanation' => 'في نموذج TCP/IP، تم دمج وظائف الطبقة السابعة والسادسة والخامسة (التطبيقات، التقديم، والجلسة) داخل طبقة واحدة تسمى طبقة التطبيقات (Application Layer).',
        'difficulty' => 'easy',
        'domain_name' => 'OSI Architecture'
    ],
    [
        'level_id' => 1,
        'topic_id' => 4,
        'question_text' => 'ما هو الترتيب الدقيق للإشارات المتبادلة في المصافحة الثلاثية (Three-Way Handshake) لإنشاء جلسة اتصال TCP جديدة؟',
        'option_a' => 'ACK ثم SYN ثم SYN-ACK',
        'option_b' => 'SYN ثم SYN-ACK ثم ACK',
        'option_c' => 'SYN ثم ACK ثم FIN',
        'option_d' => 'DATA ثم ACK ثم FIN',
        'correct_option' => 'B',
        'explanation' => 'تبدأ المصافحة بإرسال العميل إشارة SYN لطلب المزامنة، يرد الخادم بإشارة SYN-ACK للموافقة والمزامنة، وأخيراً يرد العميل بإشارة ACK لتأكيد بدء الاتصال الموثوق.',
        'difficulty' => 'easy',
        'domain_name' => 'Transport Protocols'
    ],

    // =========================================================================
    // LEVEL 2: العنونة واقتطاع الشبكات الفرعية (Subnetting, VLSM, VLANs, CIDR)
    // =========================================================================
    [
        'level_id' => 2,
        'topic_id' => 5,
        'question_text' => 'ما هو عدد العناوين الصالحة للاستخدام من قبل الأجهزة (Usable Hosts) في شبكة فرعية تملك قناع 255.255.255.224 (/27)؟',
        'option_a' => '32 جهازاً',
        'option_b' => '30 جهازاً',
        'option_c' => '62 جهازاً',
        'option_d' => '14 جهازاً',
        'correct_option' => 'B',
        'explanation' => 'قناع /27 يترك 32 - 27 = 5 بتات للمضيفين. إجمالي العناوين هو 2^5 = 32 عنواناً. نطرح عنوان الشبكة وعنوان البث العام (32 - 2) فيتبقى 30 عنواناً متاحاً للأجهزة.',
        'difficulty' => 'easy',
        'domain_name' => 'Subnetting & CIDR'
    ],
    [
        'level_id' => 2,
        'topic_id' => 5,
        'question_text' => 'إذا كان جهاز حاسوب يحمل العنوان 10.10.15.65 بقناع 255.255.255.192 (/26)، فما هو عنوان الشبكة الفرعية (Network ID) التي ينتمي إليها؟',
        'option_a' => '10.10.15.0',
        'option_b' => '10.10.15.64',
        'option_c' => '10.10.15.32',
        'option_d' => '10.10.15.128',
        'correct_option' => 'B',
        'explanation' => 'قناع /26 قيمته 192 في الثماني الرابع، مما يعطي حجم كتلة (Block Size) قدره 256 - 192 = 64. الشبكات تبدأ عند .0 ثم .64 ثم .128. العنوان .65 يقع مباشرة في شبكة 10.10.15.64/26.',
        'difficulty' => 'medium',
        'domain_name' => 'Subnetting & CIDR'
    ],
    [
        'level_id' => 2,
        'topic_id' => 6,
        'question_text' => 'ما هو عنوان البث العام (Broadcast Address) للشبكة الفرعية 192.168.1.16/29؟',
        'option_a' => '192.168.1.23',
        'option_b' => '192.168.1.24',
        'option_c' => '192.168.1.31',
        'option_d' => '192.168.1.255',
        'correct_option' => 'A',
        'explanation' => 'قناع /29 يعادل 255.255.255.248 بحجم قفزة 2^(32-29) = 8. تبدأ الشبكة عند 192.168.1.16 والشبكة التالية تبدأ عند .24، وبالتالي يكون العنوان الأخير قبل الشبكة التالية هو عنوان البث: 192.168.1.23.',
        'difficulty' => 'medium',
        'domain_name' => 'Subnetting & CIDR'
    ],
    [
        'level_id' => 2,
        'topic_id' => 7,
        'question_text' => 'عندما يفشل جهاز حاسوب في الوصول إلى خادم DHCP، ما هو نطاق العناوين الذي يولد منه عنوان APIPA تلقائياً (Link-Local)؟',
        'option_a' => '10.0.0.0/8',
        'option_b' => '169.254.0.0/16',
        'option_c' => '192.168.0.0/16',
        'option_d' => '127.0.0.0/8',
        'correct_option' => 'B',
        'explanation' => 'خاصية APIPA (Automatic Private IP Addressing) المحددة بمعيار RFC 3927 تخصص تلقائياً عنواناً من نطاق 169.254.0.1 إلى 169.254.255.254 عندما يتعذر على الجهاز استلام IP من خادم DHCP.',
        'difficulty' => 'easy',
        'domain_name' => 'IPv4 Addressing'
    ],
    [
        'level_id' => 2,
        'topic_id' => 7,
        'question_text' => 'أي من فئات العناوين التالية (Address Classes) مخصصة للبث متعدد الوجهات (Multicast) في بروتوكول IPv4؟',
        'option_a' => 'Class A (1.0.0.0 to 126.255.255.255)',
        'option_b' => 'Class B (128.0.0.0 to 191.255.255.255)',
        'option_c' => 'Class C (192.0.0.0 to 223.255.255.255)',
        'option_d' => 'Class D (224.0.0.0 to 239.255.255.255)',
        'correct_option' => 'D',
        'explanation' => 'الفئة Class D تمتد من 224.0.0.0 إلى 239.255.255.255 ومخصصة لخدمات البث متعدد الوجهات (Multicast)، مثل بروتوكولات التوجيه OSPF (224.0.0.5) وبث الوسائط المتعددة.',
        'difficulty' => 'easy',
        'domain_name' => 'IPv4 Addressing'
    ],
    [
        'level_id' => 2,
        'topic_id' => 8,
        'question_text' => 'ما هو المعيار الدولي القياسي المستخدم لإضافة وسم (VLAN Tagging) للإطارات عبر منافذ الـ Trunk في مبدلات الشبكة؟',
        'option_a' => 'Cisco ISL (Inter-Switch Link)',
        'option_b' => 'IEEE 802.1Q (Dot1Q)',
        'option_c' => 'IEEE 802.3u',
        'option_d' => 'IEEE 802.11ax',
        'correct_option' => 'B',
        'explanation' => 'معيار IEEE 802.1Q هو المعيار المفتوح المعتمد عالمياً لإضافة وسم بحجم 4 بايت يحتوي على معرف الـ VLAN (VLAN ID من 1 إلى 4094) لتمرير حركة بيانات عدة شبكات افتراضية عبر منفذ Trunk واحد.',
        'difficulty' => 'medium',
        'domain_name' => 'VLANs & Trunking'
    ],
    [
        'level_id' => 2,
        'topic_id' => 8,
        'question_text' => 'ما هو مفهوم الـ Native VLAN في روابط الـ Trunk وفق معيار 802.1Q في مبدلات سيسكو؟',
        'option_a' => 'الشبكة الافتراضية المخصصة لحركة البيانات بدون وسم (Untagged Frames)',
        'option_b' => 'الشبكة الوحيدة المسموح لها بنقل البيانات عبر المبدل',
        'option_c' => 'شبكة مخصصة فقط لأوامر سيسكو المشفرة',
        'option_d' => 'شبكة افتراضية تلغي الحاجة للراوتر',
        'correct_option' => 'A',
        'explanation' => 'الـ Native VLAN (وافتراضياً هي VLAN 1 في سيسكو) هي الشبكة التي تنقل الإطارات بدون إضافة وسم 802.1Q؛ وتعتبر مطابقة Native VLAN على طرفي رابط الـ Trunk مطلباً أمنياً أساسياً لمنع VLAN Hopping.',
        'difficulty' => 'medium',
        'domain_name' => 'VLANs & Trunking'
    ],
    [
        'level_id' => 2,
        'topic_id' => 6,
        'question_text' => 'ما هو أفضل عنوان ملخص (Route Summarization / CIDR) يجمع الشبكات الأربع التالية: 192.168.0.0/24 و 192.168.1.0/24 و 192.168.2.0/24 و 192.168.3.0/24؟',
        'option_a' => '192.168.0.0/21',
        'option_b' => '192.168.0.0/22',
        'option_c' => '192.168.0.0/23',
        'option_d' => '192.168.0.0/16',
        'correct_option' => 'B',
        'explanation' => 'القيم الثنائية للثماني الثالث: 0 (00000000), 1 (00000001), 2 (00000010), 3 (00000011). تتطابق البتات الست الأولى، فنستعير 6 بتات من الثماني الثالث: 16 + 6 = 22 بت. العنوان الملخص الدقيق هو 192.168.0.0/22.',
        'difficulty' => 'hard',
        'domain_name' => 'Subnetting & CIDR'
    ],
    [
        'level_id' => 2,
        'topic_id' => 5,
        'question_text' => 'شبكة مؤسسة تحتاج لتخصيص شبكة فرعية من 172.16.0.0/16 تستوعب 500 جهاز حاسوب على الأقل في مبنى الإدارة. ما هو أصغر قناع شبكة يحقق هذا المتطلب؟',
        'option_a' => '/24 (255.255.255.0)',
        'option_b' => '/23 (255.255.254.0)',
        'option_c' => '/22 (255.255.252.0)',
        'option_d' => '/25 (255.255.255.128)',
        'correct_option' => 'B',
        'explanation' => 'لحساب عدد البتات المطلوبة: 2^n - 2 >= 500. إذا اخترنا n = 9 بت: 2^9 - 2 = 512 - 2 = 510 أجهزة (قناع /23). بينما قناع /24 يوفر 254 جهازاً فقط وهو غير كافٍ. لذا /23 هو الحل الأمثل دون هدر.',
        'difficulty' => 'hard',
        'domain_name' => 'VLSM Design'
    ],
    [
        'level_id' => 2,
        'topic_id' => 7,
        'question_text' => 'ما هي الوظيفة الأساسية لعنوان البوابة الافتراضية (Default Gateway) المكون في إعدادات الشبكة لجهاز الحاسوب؟',
        'option_a' => 'تزويد الجهاز بعنوان IP تلقائي عبر الشبكة',
        'option_b' => 'توجيه الحزم المتجهة إلى شبكات فرعية ووجهات تقع خارج الشبكة المحلية',
        'option_c' => 'فحص الكابلات بحثاً عن انقطاع فيزيائي',
        'option_d' => 'تسريع سرعة معالج الحاسوب',
        'correct_option' => 'B',
        'explanation' => 'عندما يقارن الحاسوب عنوان وجهة الحزمة بعنوانه وقناعه ويكتشف أن الوجهة تقع في شبكة أخرى، يرسل الحزمة إلى عنوان البوابة الافتراضية (وهو منفذ الراوتر المحلي) ليتولى توجيهها.',
        'difficulty' => 'easy',
        'domain_name' => 'IPv4 Addressing'
    ],
    [
        'level_id' => 2,
        'topic_id' => 6,
        'question_text' => 'ما هو قناع البدل (Wildcard Mask) المستخدم مع قناع الشبكة 255.255.240.0 في إعدادات قوائم سيسكو؟',
        'option_a' => '0.0.15.255',
        'option_b' => '0.0.31.255',
        'option_c' => '0.0.0.240',
        'option_d' => '255.255.15.0',
        'correct_option' => 'A',
        'explanation' => 'يتم طرح قناع الشبكة من 255.255.255.255: (255-255=0), (255-255=0), (255-240=15), (255-0=255). الناتج هو 0.0.15.255.',
        'difficulty' => 'medium',
        'domain_name' => 'Subnetting & CIDR'
    ],

    // =========================================================================
    // LEVEL 3: بروتوكولات التوجيه والتبديل (OSPF, BGP, STP, EtherChannel)
    // =========================================================================
    [
        'level_id' => 3,
        'topic_id' => 10,
        'question_text' => 'كيف يتم حساب مقياس التكلفة (Cost Metric) لمنفذ الراوتر في بروتوكول OSPF وفق المعادلة الافتراضية في سيسكو؟',
        'option_a' => 'عدد القفزات (Hop Count) مقسوماً على 15',
        'option_b' => 'Reference Bandwidth (100 Mbps) مقسوماً على Interface Bandwidth',
        'option_c' => 'تأخير القناة (Delay) مضروباً في الموثوقية',
        'option_d' => 'قيمة ثابتة تساوي 10 لجميع المنافذ',
        'correct_option' => 'B',
        'explanation' => 'معادلة تكلفة OSPF الافتراضية هي: Cost = Reference Bandwidth (10^8 bps = 100 Mbps) / Interface Bandwidth bps. منفذ FastEthernet تكلفته 1، بينما 10 Mbps تكلفته 10.',
        'difficulty' => 'medium',
        'domain_name' => 'OSPF Dynamics'
    ],
    [
        'level_id' => 3,
        'topic_id' => 10,
        'question_text' => 'في شبكة OSPF متعددة الوصول (Broadcast Multiaccess)، كيف يتم انتخاب الراوتر المعين الرئيسي (Designated Router - DR)؟',
        'option_a' => 'الراوتر صاحب أقل عنوان IP للمنفذ',
        'option_b' => 'الراوتر صاحب أعلى OSPF Priority، ثم صاحب أعلى Router ID في حال التعادل',
        'option_c' => 'الراوتر المتصل مباشرة بالإنترنت فقط',
        'option_d' => 'يتم اختياره عشوائياً بواسطة خوارزمية ديكسترا',
        'correct_option' => 'B',
        'explanation' => 'يتم انتخاب DR و BDR بناءً على أعلى قيمة OSPF Priority (من 1 إلى 255، والقيمة 0 تعني عدم المشاركة)، وفي حال التعادل يفوز الراوتر صاحب أعلى Router ID.',
        'difficulty' => 'medium',
        'domain_name' => 'OSPF Dynamics'
    ],
    [
        'level_id' => 3,
        'topic_id' => 9,
        'question_text' => 'ما هو المسار الثابت العائم (Floating Static Route) وكيف يتم تكوينه في أجهزة التوجيه؟',
        'option_a' => 'مسار ديناميكي يتعلم المسارات من السحابة مباشرة',
        'option_b' => 'مسار ثابت تم تعيين مسافة إدارية له (AD) أعلى من البروتوكول الأساسي ليعمل كمسار احتياطي فقط عند تعطل المسار الرئيسي',
        'option_c' => 'مسار ثابت بدون عنوان بوابة افتراضية',
        'option_d' => 'مسار خاص ببروتوكول RIP فقط',
        'correct_option' => 'B',
        'explanation' => 'المسار الثابت العائم يُعطى مسافة إدارية أكبر (مثلاً 120 أو 130) من OSPF (110)؛ فيظل مختفياً من جدول التوجيه طالما OSPF نشط، ويظهر تلقائياً كمسار احتياطي فوري إذا تعطل مسار OSPF.',
        'difficulty' => 'hard',
        'domain_name' => 'Routing Architecture'
    ],
    [
        'level_id' => 3,
        'topic_id' => 12,
        'question_text' => 'ما هو الترتيب الصحيح للحالات الأربع التي يمر بها منفذ المبدل في بروتوكول STP التقليدي (802.1D) قبل البدء في نقل حزم المستخدم؟',
        'option_a' => 'Listening -> Blocking -> Learning -> Forwarding',
        'option_b' => 'Blocking -> Listening -> Learning -> Forwarding',
        'option_c' => 'Forwarding -> Learning -> Listening -> Disabled',
        'option_d' => 'Learning -> Listening -> Forwarding -> Blocking',
        'correct_option' => 'B',
        'explanation' => 'يمر المنفذ في STP التقليدي بحالة Blocking (حجب)، ثم Listening (استماع لرسائل BPDU لمدة 15 ثانية)، ثم Learning (بناء جدول MAC لمدة 15 ثانية)، ثم ينتقل أخيراً لحالة Forwarding لتمرير البيانات.',
        'difficulty' => 'medium',
        'domain_name' => 'Switching & STP'
    ],
    [
        'level_id' => 3,
        'topic_id' => 12,
        'question_text' => 'ما هو المعيار المطور لبروتوكول الشجرة الممتدة الذي يختصر وقت التقارب (Convergence) من 50 ثانية إلى أقل من ثانيتين؟',
        'option_a' => 'IEEE 802.1D (STP)',
        'option_b' => 'IEEE 802.1w (Rapid Spanning Tree Protocol - RSTP)',
        'option_c' => 'IEEE 802.1s (MSTP)',
        'option_d' => 'IEEE 802.3ad',
        'correct_option' => 'B',
        'explanation' => 'معيار RSTP (802.1w) يقدم آليات تفاوض استباقية (Proposal/Agreement Handshake) وحالات منافذ جديدة تختصر التقارب إلى أجزاء من الثانية بدلاً من مؤقتات STP الطويلة.',
        'difficulty' => 'easy',
        'domain_name' => 'Switching & STP'
    ],
    [
        'level_id' => 3,
        'topic_id' => 9,
        'question_text' => 'ما هي تقنية (Router-on-a-Stick) المستخدمة في توجيه حركة البيانات بين شبكات الـ VLAN المختلفة (Inter-VLAN Routing)؟',
        'option_a' => 'توصيل كابل منفصل لكل جهاز حاسوب مباشرة بالراوتر',
        'option_b' => 'استخدام منفذ فيزيائي واحد على الراوتر مقسم إلى منافذ فرعية منطقية (Sub-interfaces) مع كبسلة 802.1Q',
        'option_c' => 'إلغاء تشغيل الراوتر واستبداله بموزع قديم Hub',
        'option_d' => 'استخدام اتصال لاسلكي حصري',
        'correct_option' => 'B',
        'explanation' => 'تعتمد طريقة Router-on-a-Stick على إنشاء Sub-interfaces (مثل g0/0.10 و g0/0.20) وتفعيل أمر encapsulation dot1Q لتعمل كل منها كبوابة افتراضية لشبكة VLAN مستقلة عبر كابل Trunk واحد.',
        'difficulty' => 'medium',
        'domain_name' => 'VLAN Routing'
    ],
    [
        'level_id' => 3,
        'topic_id' => 12,
        'question_text' => 'ما هو البروتوكول المعياري المفتوح المعتمد من IEEE لتجميع عدة روابط فيزيائية في رابط منطقي فائق السرعة (EtherChannel)؟',
        'option_a' => 'PAgP (Port Aggregation Protocol)',
        'option_b' => 'LACP (Link Aggregation Control Protocol - IEEE 802.3ad)',
        'option_c' => 'VTP (VLAN Trunking Protocol)',
        'option_d' => 'CDP (Cisco Discovery Protocol)',
        'correct_option' => 'B',
        'explanation' => 'بروتوكول LACP هو المعيار الصناعي المفتوح (IEEE 802.3ad / 802.1AX) لتجميع ما يصل إلى 8 روابط فيزيائية نشطة في رابط EtherChannel واحد، بينما PAgP هو بروتوكول خاص بسيسكو.',
        'difficulty' => 'easy',
        'domain_name' => 'EtherChannel Dynamics'
    ],
    [
        'level_id' => 3,
        'topic_id' => 10,
        'question_text' => 'ما وظيفة الأمر `passive-interface` عند تطبيقه على منفذ في إعدادات بروتوكول OSPF على راوتر سيسكو؟',
        'option_a' => 'تعطيل المنفذ فيزيائياً وإيقاف مرور الكهرباء فيه',
        'option_b' => 'إيقاف إرسال رسائل OSPF Hello عبر المنفذ مع استمرار الإعلان عن شبكة المنفذ لباقي الراوترات',
        'option_c' => 'تحويل المنفذ للعمل كـ Default Gateway فقط',
        'option_d' => 'تشفير جميع الحزم الخارجة من المنفذ',
        'correct_option' => 'B',
        'explanation' => 'استخدام passive-interface على المنافذ المتصلة بأجهزة المستخدمين النهائية يمنع إرسال رسائل Hello غير الضرورية ويوفر الموارد ويعزز الأمان لمنع اختراق جيران OSPF غير المصرح بهم.',
        'difficulty' => 'medium',
        'domain_name' => 'OSPF Dynamics'
    ],
    [
        'level_id' => 3,
        'topic_id' => 12,
        'question_text' => 'ما هي الفائدة الهندسية المباشرة من تفعيل ميزة `spanning-tree portfast` على منافذ مبدل سيسكو المتصلة بأجهزة الحاسوب؟',
        'option_a' => 'مضاعفة سرعة النقل من 1 Gbps إلى 10 Gbps',
        'option_b' => 'نقل المنفذ فوراً إلى حالة Forwarding متجاوزاً فترات الاستماع والتعلم (30 ثانية)',
        'option_c' => 'حجب هجمات حجب الخدمة تلقائياً',
        'option_d' => 'توزيع عناوين IP تلقائياً بدون DHCP',
        'correct_option' => 'B',
        'explanation' => 'تسمح ميزة PortFast للأجهزة الطرفية (Access Ports) بالحصول على اتصال فوري بالشبكة دون انتظار الـ 30 ثانية الخاصة بحالات Listening و Learning، مما يمنع مشاكل مهلة استلام عنوان DHCP.',
        'difficulty' => 'easy',
        'domain_name' => 'Switching & STP'
    ],
    [
        'level_id' => 3,
        'topic_id' => 11,
        'question_text' => 'ما هو المعرّف الفريد عالمياً المستخدم لتمييز الأنظمة المستقلة وتوجيه البيانات بينها عبر بروتوكول BGP في شبكة الإنترنت؟',
        'option_a' => 'MAC Address',
        'option_b' => 'Autonomous System Number (ASN)',
        'option_c' => 'VLAN ID',
        'option_d' => 'Default Gateway ID',
        'correct_option' => 'B',
        'explanation' => 'رقم النظام المستقل (ASN) هو رقم فريد عالمياً يتم تخصيصه من قبل هيئة IANA/RIRs للشركات ومزودي خدمة الإنترنت (ISPs) لتمكين بروتوكول BGP من توجيه حركة البيانات العالمية بدقة.',
        'difficulty' => 'medium',
        'domain_name' => 'BGP & Internet Routing'
    ],
    [
        'level_id' => 3,
        'topic_id' => 9,
        'question_text' => 'إذا تعلم الراوتر مسارين لنفس الشبكة الفرعية تماماً: أحدهما عبر OSPF والآخر عبر EIGRP الداخلي، فأي مسار سيختاره؟',
        'option_a' => 'مسار OSPF لأن خوارزميته مفتوحة المصدر',
        'option_b' => 'مسار EIGRP لأن مسافته الإدارية (AD = 90) أقل من OSPF (AD = 110)',
        'option_c' => 'يتم تقسيم الحزم مناصفة بينهما',
        'option_d' => 'يرفض الراوتر كلا المسارين ويعلن عن خطأ',
        'correct_option' => 'B',
        'explanation' => 'الراوتر يقارن المسافة الإدارية (Administrative Distance) أولاً قبل أي مقياس آخر؛ وبما أن AD لبروتوكول EIGRP الداخلي هي 90 مقارنة بـ 110 لبروتوكول OSPF، فإن مسار EIGRP هو الفائز ويوضع في جدول التوجيه.',
        'difficulty' => 'medium',
        'domain_name' => 'Routing Architecture'
    ],

    // =========================================================================
    // LEVEL 4: أمن الشبكات والجدران النارية (ACLs, NAT/PAT, VPN, Zero Trust)
    // =========================================================================
    [
        'level_id' => 4,
        'topic_id' => 13,
        'question_text' => 'ما هو النطاق الرقمي القياسي المخصص لقوائم التحكم بالوصول الموسعة (Extended Numbered ACLs) في راوترات سيسكو؟',
        'option_a' => 'من 1 إلى 99 ومن 1300 إلى 1999',
        'option_b' => 'من 100 إلى 199 ومن 2000 إلى 2699',
        'option_c' => 'من 500 إلى 599 فقط',
        'option_d' => 'من 1000 إلى 1099',
        'correct_option' => 'B',
        'explanation' => 'في راوترات سيسكو، الأرقام 1-99 و 1300-1999 مخصصة لـ Standard ACL، بينما الأرقام 100-199 و 2000-2699 مخصصة لـ Extended ACL التي تفحص المصدر والوجهة والبروتوكول وأرقام المنافذ.',
        'difficulty' => 'easy',
        'domain_name' => 'Access Control Lists'
    ],
    [
        'level_id' => 4,
        'topic_id' => 14,
        'question_text' => 'ما هي تقنية ترجمة العناوين التي تسمح لآلاف الأجهزة في الشبكة الداخلية بمشاركة عنوان IPv4 عمومي واحد للوصول إلى الإنترنت؟',
        'option_a' => 'Static NAT (1-to-1)',
        'option_b' => 'Port Address Translation (PAT / NAT Overload)',
        'option_c' => 'Dynamic NAT Pool',
        'option_d' => 'Proxy ARP',
        'correct_option' => 'B',
        'explanation' => 'تقنية PAT (تسمى أيضاً NAT Overload) تعين لكل اتصال داخلي رقم منفذ مصدر فريد (Source Port) على نفس عنوان الـ IP العمومي، مما يتيح لأكثر من 65,000 جلسة متزامنة مشاركة عنوان IP عام واحد.',
        'difficulty' => 'medium',
        'domain_name' => 'NAT & Translation'
    ],
    [
        'level_id' => 4,
        'topic_id' => 16,
        'question_text' => 'في ميزة Port Security على مبدلات سيسكو، أي وضع انتهاك (Violation Mode) يقوم بإيقاف المنفذ تماماً ونقله لحالة err-disable؟',
        'option_a' => 'Protect Mode',
        'option_b' => 'Restrict Mode',
        'option_c' => 'Shutdown Mode',
        'option_d' => 'Bypass Mode',
        'correct_option' => 'C',
        'explanation' => 'وضع Shutdown هو الوضع الافتراضي والأكثر صرامة؛ فعند اتصال جهاز بعنوان MAC غير مصرح به، يقوم المبدل بإسقاط الحزم فوراً وإرسال تنبيه SNMP ونقل المنفذ لحالة err-disable حتى يعاد تشغيله يدوياً.',
        'difficulty' => 'medium',
        'domain_name' => 'Switch Security'
    ],
    [
        'level_id' => 4,
        'topic_id' => 16,
        'question_text' => 'ما هو الهجوم الأمني الخطير في الطبقة الثانية الذي صُممت ميزة DHCP Snooping لمنعه وحماية المستخدمين منه؟',
        'option_a' => 'هجوم خادم DHCP المخادع (Rogue DHCP Server) واستنزاف العناوين (DHCP Starvation)',
        'option_b' => 'هجمات حجب الخدمة على كابلات الألياف الضوئية',
        'option_c' => 'تجاوز سعة بطاقة الذاكرة في الراوتر',
        'option_d' => 'سرقة كلمة سر شاشة القفل للحاسوب',
        'correct_option' => 'A',
        'explanation' => 'تقسم ميزة DHCP Snooping منافذ المبدل إلى Trusted (متصلة بخادم DHCP الشرعي) و Untrusted (منافذ المستخدمين) وتسقط فوراً أي رسائل DHCP Offer/ACK قادمة من منافذ المستخدمين غير الموثوقة لمنع هجمات Man-in-the-Middle.',
        'difficulty' => 'medium',
        'domain_name' => 'Switch Security'
    ],
    [
        'level_id' => 4,
        'topic_id' => 15,
        'question_text' => 'ما هو الهدف الأساسي من المرحلة الأولى (Phase 1 / IKE SA) في مفاوضات إنشاء نفق IPsec VPN؟',
        'option_a' => 'نقل ملفات المستخدمين غير المشفرة مباشرة',
        'option_b' => 'إنشاء نفق اتصال آمن ومشفر للمصادقة وحماية مفاوضات معايير النفق في المرحلة الثانية',
        'option_c' => 'حساب عناوين الـ MAC Address للأجهزة البعيدة',
        'option_d' => 'تثبيت برامج مكافحة الفيروسات على الراوترات',
        'correct_option' => 'B',
        'explanation' => 'في Phase 1 (IKEv1 / IKEv2)، يتفاوض الطرفان على خوارزميات التشفير (AES)، والتجزئة (SHA)، والمصادقة (PSK/Certificates)، ومجموعة Diffie-Hellman لإنشاء نفق ISAKMP SA الآمن الذي تُدار من خلاله مفاوضات Phase 2.',
        'difficulty' => 'hard',
        'domain_name' => 'VPN & IPsec'
    ],
    [
        'level_id' => 4,
        'topic_id' => 14,
        'question_text' => 'كيف تساهم جدران الحماية المتقدمة (Stateful Firewalls) في صد هجمات حجب الخدمة من نوع TCP SYN Flood؟',
        'option_a' => 'بإلغاء بروتوكول TCP من الشبكة بالكامل',
        'option_b' => 'باستخدام آلية SYN Cookies ومراقبة جدول الجلسات النشطة لتفقد الاتصالات غير المكتملة',
        'option_c' => 'بزيادة سعة القرص الصلب في السيرفر',
        'option_d' => 'بفصل كابل الكهرباء عن الخادم',
        'correct_option' => 'B',
        'explanation' => 'تستخدم جدران الحماية تقنية SYN Cookies أو فحص الاتصالات نصف المفتوحة (Half-Open Connections) حيث لا تحجز موارد الذاكرة للاتصال إلا بعد إتمام المصافحة الثلاثية بوصول إشارة الـ ACK الشرعية من العميل.',
        'difficulty' => 'hard',
        'domain_name' => 'Firewalls & Defense'
    ],
    [
        'level_id' => 4,
        'topic_id' => 16,
        'question_text' => 'ما هي الركائز الثلاث الأساسية التي يشير إليها مثلث أمن المعلومات الشهير (CIA Triad)؟',
        'option_a' => 'Cables, Internet, Access',
        'option_b' => 'Confidentiality (السرية), Integrity (النزاهة), Availability (التوافرية)',
        'option_c' => 'Control, Inspection, Authentication',
        'option_d' => 'Cryptography, IP, Antivirus',
        'correct_option' => 'A', // Will be B! Let's ensure B is the answer!
        'correct_option' => 'B',
        'explanation' => 'مثلث CIA هو النموذج المعياري لأمن المعلومات: السرية (Confidentiality - حصر الوصول للمصرح لهم)، النزاهة (Integrity - ضمان عدم تزوير البيانات)، والتوافرية (Availability - ضمان إتاحة الخدمات عند طلبها).',
        'difficulty' => 'easy',
        'domain_name' => 'Security Fundamentals'
    ],
    [
        'level_id' => 4,
        'topic_id' => 16,
        'question_text' => 'على ماذا تعتمد ميزة الفحص الديناميكي لحزم ARP (Dynamic ARP Inspection - DAI) في التحقق من صحة حزم ARP ومنع هجمات التسميم ARP Poisoning؟',
        'option_a' => 'قاعدة بيانات عناوين الـ DNS العالمية',
        'option_b' => 'جدول الربط الناتج عن ميزة DHCP Snooping (DHCP Snooping Binding Database)',
        'option_c' => 'سرعة المعالج في الراوتر الرئيسي',
        'option_d' => 'قائمة كلمات السر في نظام التشغيل',
        'correct_option' => 'B',
        'explanation' => 'تقوم ميزة DAI بمقارنة عنوان IP وعنوان MAC وحقل المنفذ في كل حزمة ARP بالبيانات المخزنة مسبقاً في جدول DHCP Snooping Binding Table، وتسقط الحزمة فوراً إذا كانت بياناتها غير مطابقة لمنع هجمات التسميم.',
        'difficulty' => 'hard',
        'domain_name' => 'Switch Security'
    ],
    [
        'level_id' => 4,
        'topic_id' => 15,
        'question_text' => 'ما هو الفارق الجوهري بين التشفير المتماثل (Symmetric Encryption) والتشفير غير المتماثل (Asymmetric Encryption)؟',
        'option_a' => 'المتماثل يستخدم نفس المفتاح للتشفير وفك التشفير، بينما غير المتماثل يستخدم زوج مفاتيح (عام للتشفير وخاص لفك التشفير)',
        'option_b' => 'غير المتماثل أسرع بآلاف المرات من المتماثل',
        'option_c' => 'المتماثل لا يحتاج لأي مفاتيح رياضية',
        'option_d' => 'غير المتماثل يستخدم فقط في شبكات البلوتوث',
        'correct_option' => 'A',
        'explanation' => 'التشفير المتماثل (مثل AES) سريع جداً ويستخدم نفس المفتاح السري، بينما غير المتماثل (مثل RSA و ECC) يستخدم مفتاحاً عاماً (Public Key) معلناً ومفتاحاً خاصاً (Private Key) سرياً لحل معضلة توزيع المفاتيح.',
        'difficulty' => 'medium',
        'domain_name' => 'Cryptography & Security'
    ],
    [
        'level_id' => 4,
        'topic_id' => 16,
        'question_text' => 'وفق منهجية الأمان الحديثة (Zero Trust Architecture)، ما هو المبدأ الحاكم عند محاولة جهاز موظف الاتصال بخادم قاعدة بيانات داخل المقر الرئيسي للشركة؟',
        'option_a' => 'منحه الثقة الكاملة بدون فحص لأنه متصل بالشبكة السلكية الداخلية للمكتب',
        'option_b' => 'إلزام الجهاز والمستخدم بالمصادقة الصارمة والتحقق من صحة الجهاز وتطبيق مبدأ الصلاحيات الأدنى (Least Privilege) باستمرار',
        'option_c' => 'إيقاف تشغيل الخادم فوراً للاشتباه بالأمان',
        'option_d' => 'السماح له بالدخول إذا كان اسم المستخدم يبدأ بحرف إنجليزي',
        'correct_option' => 'B',
        'explanation' => 'منهجية Zero Trust تنص على: Never Trust, Always Verify. أي أنه لا توجد منطقة موثوقة حتى داخل محيط الشبكة الداخلية، ويلزم التحقق المستمر من الهوية وسلوك الجهاز وموقع الاتصال.',
        'difficulty' => 'easy',
        'domain_name' => 'Zero Trust Architecture'
    ],
    [
        'level_id' => 4,
        'topic_id' => 13,
        'question_text' => 'في راوتر سيسكو، ما هو الفرق الأساسي في التطبيق بين الأمر `ip access-group 101 in` والأمر `ip access-group 101 out` على منفذ الواجهة؟',
        'option_a' => 'الأمر `in` يفحص الحزم الواردة إلى الراوتر قبل اتخاذ قرار التوجيه، بينما `out` يفحص الحزم بعد توجيهها وقبل خروجها من المنفذ',
        'option_b' => 'الأمر `in` يخص بروتوكول IPv6 فقط بينما `out` يخص IPv4',
        'option_c' => 'الأمران متطابقان تماماً ولا يوجد أي فارق في الأداء',
        'option_d' => 'الأمر `out` يقوم بمسح الـ ACL نهائياً من الذاكرة',
        'correct_option' => 'A',
        'explanation' => 'تطبيق ACL كـ `in` يفحص الحزمة فور دخولها من المنفذ ويسقطها قبل استهلاك موارد المعالج في عملية التوجيه، بينما `out` يفحص الحزمة بعد أن حدد الراوتر مسارها وأصبحت جاهزة للإرسال عبر ذلك المنفذ.',
        'difficulty' => 'medium',
        'domain_name' => 'Access Control Lists'
    ]
];

echo "Starting insertion of " . count($questions) . " advanced questions...\n";

$ins_stmt = $pdo->prepare("
    INSERT INTO `questions` 
    (`level_id`, `topic_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `explanation`, `difficulty`, `domain_name`, `duration_minutes`) 
    VALUES 
    (:level_id, :topic_id, :question_text, :option_a, :option_b, :option_c, :option_d, :correct_option, :explanation, :difficulty, :domain_name, 15)
");

$inserted = 0;
foreach ($questions as $q) {
    // Check if duplicate question exists
    $chk = $pdo->prepare("SELECT id FROM questions WHERE question_text = ?");
    $chk->execute([$q['question_text']]);
    if ($chk->fetch()) {
        continue;
    }

    $ins_stmt->execute([
        ':level_id' => $q['level_id'],
        ':topic_id' => $q['topic_id'],
        ':question_text' => $q['question_text'],
        ':option_a' => $q['option_a'],
        ':option_b' => $q['option_b'],
        ':option_c' => $q['option_c'],
        ':option_d' => $q['option_d'],
        ':correct_option' => $q['correct_option'],
        ':explanation' => $q['explanation'],
        ':difficulty' => $q['difficulty'],
        ':domain_name' => $q['domain_name']
    ]);
    $inserted++;
}

echo "Successfully inserted {$inserted} new questions!\n";

$total = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
echo "New Total Questions in Database: {$total}\n";

$levels = $pdo->query("SELECT level_id, COUNT(*) as c FROM questions GROUP BY level_id")->fetchAll(PDO::FETCH_KEY_PAIR);
foreach ($levels as $lvl => $cnt) {
    echo "Level {$lvl}: {$cnt} questions\n";
}
