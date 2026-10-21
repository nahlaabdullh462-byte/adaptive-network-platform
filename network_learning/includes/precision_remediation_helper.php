<?php
/**
 * Precision Remediation & Bypass Engine (نظام التجاوز مع المعالجة الدقيقة)
 * NetLearn Pro - Adaptive Educational Intelligence
 */

if (!function_exists('get_topic_micro_capsule')) {
    /**
     * كبسولات المراجعة السريعة (30 ثانية) لمفاهيم الشبكات الأساسية
     */
    function get_topic_micro_capsule($topic_id, $topic_name = '') {
        $capsules = [
            1 => [
                'concept_name' => 'نموذج OSI والطبقات السبع (OSI 7 Layers)',
                'summary_30s' => 'نموذج OSI يقسم الاتصال إلى 7 طبقات متسلسلة من الطبقة 1 (الفيزيائية) حتى الطبقة 7 (التطبيقات). البيانات تتحول من بيانات مجردة (Data) في الطبقات العليا، إلى قطع (Segments) في طبقة النقل، ثم حزم (Packets) في طبقة الشبكة، ثم إطارات (Frames) في طبقة ربط البيانات، وصولاً للبتات (Bits) في الطبقة الفيزيائية.',
                'golden_rule' => 'احفظ اختصار الترتيب: (All People Seem To Need Data Processing) من 7 إلى 1، أو (Please Do Not Throw Sausage Pizza Away) من 1 إلى 7. الراوتر يعمل في Layer 3 (Packets/IPs)، والمبدل Switch في Layer 2 (Frames/MACs).',
                'common_slip' => 'الخلط بين وحدة البيانات PDU في كل طبقة: Transport = Segment, Network = Packet, Data Link = Frame.',
                'time_seconds' => 30
            ],
            2 => [
                'concept_name' => 'أجهزة ومعدات الشبكة (Hub vs Switch vs Router)',
                'summary_30s' => 'الموزع (Hub) جهاز غبي في Layer 1 يكرر الإشارة لكل المنافذ (Collision Domain واحد). المبدل (Switch) جهاز ذكي في Layer 2 يقرأ عناوين MAC ويبني جدول CAM، فيعطي كل منفذ نطاق تصادم مستقل. الراوتر (Router) جهاز في Layer 3 يربط شبكات مختلفة، يفصل نطاقات البث العام (Broadcast Domains) ويمنع مرور الـ Broadcast افتراضياً.',
                'golden_rule' => 'المبدل يفصل الـ Collision Domains لكنه ينشر الـ Broadcast. الراوتر فقط هو من يفصل نطاقات الـ Broadcast Domains!',
                'common_slip' => 'الاعتقاد بأن المبدل العادي يحجب البث العام؛ المبدل ينشر البث العام لجميع منافذ الـ VLAN.',
                'time_seconds' => 30
            ],
            3 => [
                'concept_name' => 'كوابل الشبكة والطبقة الفيزيائية (UTP & Standards)',
                'summary_30s' => 'أقصى طول معياري لكابل الإيثرنت النحاسي UTP (Cat5e/Cat6) هو 100 متر قبل حدوث التوهين (Attenuation). الكابل المستقيم (Straight-Through) يربط أجهزة مختلفة (PC إلى Switch، أو Router إلى Switch)، بينما الكابل المتعاكس (Crossover) كان يستخدم قديماً لربط أجهزة متماثلة (PC إلى PC، أو Switch إلى Switch)، إلا أن ميزة Auto-MDIX الحديثة تقوم بالتبديل تلقائياً.',
                'golden_rule' => 'الحد الأقصى للنحاس 100 متر (90 متر كابل ممدد + 10 أمتار وصلات طرفية Patch Cords).',
                'common_slip' => 'نسيان معيار الـ 100 متر أو الخلط بين فئات Cat6 (حتى 10 Gbps لمسافات قصيرة) و Cat5e (1 Gbps).',
                'time_seconds' => 30
            ],
            4 => [
                'concept_name' => 'بروتوكولات طبقة النقل (TCP vs UDP)',
                'summary_30s' => 'بروتوكول TCP موثوق وموجه بالاتصال (Connection-Oriented) يعتمد على المصافحة الثلاثية (SYN -> SYN-ACK -> ACK) ويحتوي على أرقام تسلسلية وإعادة إرسال عند الفقد. بروتوكول UDP غير موثوق وبدون اتصال (Connectionless) يتميز بالسرعة العالية وعدم وجود عبء ترويسة (Header Overhead)، لذا يناسب البث الحي والفيديو واستعلامات DNS السريعة.',
                'golden_rule' => 'إذا كان التطبيق يتطلب الدقة دون فقد (مثل Web/HTTP, File Transfer/FTP) نستخدم TCP. إذا كان يتطلب السرعة والزمن الحقيقي الحساس للتأخير (VoIP, Video Streaming, DNS) نستخدم UDP.',
                'common_slip' => 'نسيان ترتيب المصافحة الثلاثية: العميل يرسل SYN، الخادم يرد SYN-ACK، العميل يكمل بـ ACK.',
                'time_seconds' => 30
            ],
            5 => [
                'concept_name' => 'بنية عناوين IPv4 وفئاتها (IPv4 & Private IPs)',
                'summary_30s' => 'عنوان IPv4 يتكون من 32 بت مقسمة لـ 4 ثمانيات (Octets). العناوين الخاصة المحجوزة للشبكات الداخلية (RFC 1918) ولا يمكن توجيهها على الإنترنت هي: فئة A: من 10.0.0.0 إلى 10.255.255.255 (/8)، فئة B: من 172.16.0.0 إلى 172.31.255.255 (/12)، وفئة C: من 192.168.0.0 إلى 192.168.255.255 (/16).',
                'golden_rule' => 'العناوين 127.0.0.0/8 محجوزة للاختبار الذاتي (Loopback)، وعناوين 169.254.0.0/16 محجوزة للتعيين التلقائي عند فشل DHCP (تسمى APIPA).',
                'common_slip' => 'الخلط بين نطاق الـ Private IP لفئة B (يبدأ من 172.16 وينتهي عند 172.31، وليس 172.16 إلى 172.16 فقط).',
                'time_seconds' => 30
            ],
            6 => [
                'concept_name' => 'حساب أقنعة الشبكة والـ CIDR (Subnet Mask & Wildcard)',
                'summary_30s' => 'قناع الشبكة يحدد أين ينتهي جزء الشبكة (Network Bits) وأين يبدأ جزء الأجهزة (Host Bits). عدد العناوين الصالحة للأجهزة في أي بادئة هو: (2 أس عدد بتات المضيف) ناقص 2 (عنوان الشبكة وعنوان البث). قناع البدل (Wildcard Mask) هو المعكوس الحسابي الدقيق لقناع الشبكة، ويحسب بطرح القناع من 255.255.255.255.',
                'golden_rule' => 'لحساب حجم القفزة (Block Size): اطرح قيمة الثماني المتغير في القناع من 256. مثال: قناع 255.255.255.240 (/28) -> حجم القفزة هو 256 - 240 = 16.',
                'common_slip' => 'نسيان طرح 2 (الشبكة والبث) عند السؤال عن الأجهزة الصالحة (Usable Hosts).',
                'time_seconds' => 30
            ],
            7 => [
                'concept_name' => 'تجزئة الشبكات Subnetting (Network ID & Broadcast)',
                'summary_30s' => 'في أي شبكة فرعية: أول عنوان هو عنوان الشبكة (Network ID) وتكون بتات المضيف فيه كلها أصفار (0s). آخر عنوان هو عنوان البث العام (Broadcast Address) وتكون بتات المضيف فيه كلها واحدات (1s). العناوين الواقعة بينهما فقط هي التي يمكن إسنادها للأجهزة.',
                'golden_rule' => 'البوابة الافتراضية (Default Gateway) هي منفذ الراوتر في نفس الشبكة المحلية، ويجب أن يملك IP يقع في نفس نطاق الشبكة الفرعية للأجهزة لكي تتمكن من الوصول للشبكات الخارجية.',
                'common_slip' => 'إعطاء جهاز عنوان البث أو عنوان الشبكة؛ هذان العنوانان لا يجوز تعيينهما لأي كارت شبكة.',
                'time_seconds' => 30
            ],
            8 => [
                'concept_name' => 'الشبكات الافتراضية VLANs وروابط الـ Trunk',
                'summary_30s' => 'تقنية الـ VLAN تقسم المبدل الفيزيائي الواحد إلى شبكات منطقية معزولة، حيث تمثل كل VLAN نطاق بث عام (Broadcast Domain) منفصل. للربط بين المبدلات ونقل ترافيك عدة شبكات VLAN عبر كابل واحد نستخدم رابط الـ Trunk مع بروتوكول الوسم القياسي IEEE 802.1Q.',
                'golden_rule' => 'الـ Native VLAN (وافتراضياً هي VLAN 1) ترسل إطاراتها عبر الـ Trunk بدون وسم (Untagged)، ويجب تطابقها بين طرفي الرابط لتجنب الثغرات.',
                'common_slip' => 'الاعتقاد بأن أجهزة في VLAN مختلفة يمكنها التحدث مباشرة عبر المبدل نفسه؛ التواصل بين الـ VLANs يتطلب حصراً جهاز Layer 3 (راوتر أو Multilayer Switch).',
                'time_seconds' => 30
            ]
        ];

        if (isset($capsules[$topic_id])) {
            return $capsules[$topic_id];
        }

        // كبسولة افتراضية ذكية لأي موضوع آخر
        return [
            'concept_name' => !empty($topic_name) ? $topic_name : 'مفاهيم الشبكات الأساسية',
            'summary_30s' => 'المفاهيم الأساسية في الشبكات تعتمد على المعايير القياسية المنظمة لتبادل البيانات (RFCs)، وتحديد المسارات وعناوين الأجهزة وقواعد الأمان لضمان سلامة ووصول الحزم بين الأنظمة المترابطة.',
            'golden_rule' => 'دائماً حدد الطبقة المعنية في نموذج OSI ونوع المعطيات (IP أو MAC أو Port) قبل اتخاذ القرار الهندسي.',
            'common_slip' => 'الاستعجال في قراءة شروط السؤال أو إغفال استثناءات الأقنعة والعناوين المحجوزة.',
            'time_seconds' => 30
        ];
    }
}

if (!function_exists('evaluate_diagnostic_performance')) {
    /**
     * محرك التقييم التكيفي الموزون وتشخيص الخبير مع الثغرات الأساسية
     */
    function evaluate_diagnostic_performance($pdo, $user_id, $all_attempt_questions, $levels) {
        $level_evaluations = [];
        $level_weights = [
            1 => 1.0, // مبتدئ: وزن 1
            2 => 2.0, // متوسط: وزن 2
            3 => 3.0, // متقدم: وزن 3
            4 => 4.0  // خبير: وزن 4
        ];

        $total_weighted_points_possible = 0;
        $total_weighted_points_earned = 0;
        $highest_mastered_level_id = 1;
        $highest_mastered_level_order = 1;
        $highest_mastered_level_name = 'المستوى الأول';

        $raw_total_questions = count($all_attempt_questions);
        $raw_correct_count = 0;

        $unpassed_levels = [];
        $passed_levels = [];
        $errors_by_level = [];

        foreach ($levels as $lvl) {
            $lvl_id = (int)$lvl['id'];
            $lvl_order = (int)$lvl['level_order'];
            $weight = $level_weights[$lvl_order] ?? 1.0;

            $lvl_q = array_filter($all_attempt_questions, function($item) use ($lvl_id) {
                return (int)$item['level_id'] === $lvl_id;
            });

            $total_q = count($lvl_q);
            $correct_q = 0;
            $lvl_errors = [];

            foreach ($lvl_q as $item) {
                $total_weighted_points_possible += $weight;
                if (!empty($item['is_correct'])) {
                    $correct_q++;
                    $raw_correct_count++;
                    $total_weighted_points_earned += $weight;
                } else {
                    $lvl_errors[] = $item;
                }
            }

            $percentage = ($total_q > 0) ? round(($correct_q / $total_q) * 100) : 0;
            $is_passed = ($percentage >= 75);

            if ($is_passed) {
                $passed_levels[] = $lvl_id;
                if ($lvl_order >= $highest_mastered_level_order) {
                    $highest_mastered_level_order = $lvl_order;
                    $highest_mastered_level_id = $lvl_id;
                    $highest_mastered_level_name = $lvl['level_name'];
                }
            } else {
                $unpassed_levels[] = $lvl_id;
            }

            $errors_by_level[$lvl_id] = $lvl_errors;

            $level_evaluations[$lvl_id] = [
                'id' => $lvl_id,
                'name' => $lvl['level_name'],
                'order' => $lvl_order,
                'percentage' => $percentage,
                'correct' => $correct_q,
                'total' => $total_q,
                'is_passed' => $is_passed,
                'weight' => $weight,
                'errors' => $lvl_errors
            ];
        }

        $weighted_score = ($total_weighted_points_possible > 0) 
            ? round(($total_weighted_points_earned / $total_weighted_points_possible) * 100) 
            : 0;

        $raw_percentage = ($raw_total_questions > 0) 
            ? round(($raw_correct_count / $raw_total_questions) * 100) 
            : 0;

        // التحقق من حالة: "خبير / متقدم أخطأ في الأساسيات"
        // شروطها:
        // 1. أظهر استيعاباً ممتازاً (Passed) في مستوى متقدم (المستوى 3 أو 4، أو على الأقل المستوى 2 بنسبة مرتفعة)
        // 2. ولديه خطأ أو إخفاق نسبي في مستوى أدنى (المستوى 1 أو 2)
        $is_expert_candidate = false;
        $primary_gap = null;

        // هل اجتاز مستوى 3 أو 4؟ أو هل اجتاز مستوى أعلى من مستوى به خطأ؟
        if (!empty($passed_levels)) {
            $max_passed_order = $highest_mastered_level_order;
            // فحص هل هناك مستويات ذات ترتيب أقل من max_passed_order وفيها أخطاء
            foreach ($levels as $lvl) {
                $check_order = (int)$lvl['level_order'];
                $check_id = (int)$lvl['id'];
                if ($check_order < $max_passed_order && !empty($errors_by_level[$check_id])) {
                    $is_expert_candidate = true;
                    // اختيار أول خطأ أساسي لمعالجته بدقة
                    if ($primary_gap === null && !empty($errors_by_level[$check_id])) {
                        $first_err = $errors_by_level[$check_id][0];
                        $q_info_stmt = $pdo->prepare("SELECT q.*, t.topic_name FROM questions q LEFT JOIN topics t ON q.topic_id = t.id WHERE q.id = ?");
                        $q_info_stmt->execute([$first_err['question_id']]);
                        $q_info = $q_info_stmt->fetch();

                        $capsule = get_topic_micro_capsule($q_info['topic_id'] ?? 1, $q_info['topic_name'] ?? '');

                        $primary_gap = [
                            'level_id' => $check_id,
                            'level_name' => $lvl['level_name'],
                            'level_order' => $check_order,
                            'topic_id' => $q_info['topic_id'] ?? 1,
                            'topic_name' => $q_info['topic_name'] ?? 'أساسيات الشبكات',
                            'concept_name' => $capsule['concept_name'],
                            'question_id' => $first_err['question_id'],
                            'question_text' => $q_info['question_text'] ?? '',
                            'capsule' => $capsule
                        ];
                    }
                }
            }
        }

        // إذا لم يكن خبيراً بهفوات، المستوى الموصى به هو أول مستوى لم يجتزه
        $recommended_level_id = $highest_mastered_level_id;
        if (!$is_expert_candidate) {
            $recommended_level_id = !empty($unpassed_levels) ? $unpassed_levels[0] : end($levels)['id'];
        }

        return [
            'is_expert_candidate' => $is_expert_candidate,
            'highest_mastered_level_id' => $highest_mastered_level_id,
            'highest_mastered_level_order' => $highest_mastered_level_order,
            'highest_mastered_level_name' => $highest_mastered_level_name,
            'recommended_level_id' => $recommended_level_id,
            'primary_gap' => $primary_gap,
            'level_evaluations' => $level_evaluations,
            'weighted_score' => $weighted_score,
            'raw_percentage' => $raw_percentage,
            'total_correct' => $raw_correct_count,
            'total_questions' => $raw_total_questions
        ];
    }
}

if (!function_exists('get_remediation_verification_question')) {
    /**
     * اختيار سؤال تحقق فوري في نفس المفهوم من المستوى المبتدئ/الأساسي
     */
    function get_remediation_verification_question($pdo, $topic_id, $exclude_question_ids = []) {
        $params = [$topic_id];
        $exclude_sql = "";
        if (!empty($exclude_question_ids)) {
            $placeholders = implode(',', array_fill(0, count($exclude_question_ids), '?'));
            $exclude_sql = " AND id NOT IN ($placeholders)";
            $params = array_merge($params, array_map('intval', $exclude_question_ids));
        }

        $stmt = $pdo->prepare("
            SELECT * FROM questions 
            WHERE topic_id = ? $exclude_sql 
            ORDER BY RAND() 
            LIMIT 1
        ");
        $stmt->execute($params);
        $q = $stmt->fetch(PDO::FETCH_ASSOC);

        // إذا لم نجد سؤالاً غير مجاب في نفس الموضوع، جلب أي سؤال من نفس الموضوع
        if (!$q) {
            $stmt2 = $pdo->prepare("SELECT * FROM questions WHERE topic_id = ? ORDER BY RAND() LIMIT 1");
            $stmt2->execute([$topic_id]);
            $q = $stmt2->fetch(PDO::FETCH_ASSOC);
        }

        // كإجراء احتياطي أخير: جلب سؤال من المستوى الأول
        if (!$q) {
            $q = $pdo->query("SELECT * FROM questions WHERE level_id = 1 ORDER BY RAND() LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        }

        return $q;
    }
}

if (!function_exists('get_side_quest_questions')) {
    /**
     * جلب أسئلة المسار الجانبي (Side Quest) لتقوية الثغرة المحددة (3-4 أسئلة)
     */
    function get_side_quest_questions($pdo, $topic_id, $limit = 4) {
        $stmt = $pdo->prepare("
            SELECT * FROM questions 
            WHERE topic_id = ? 
            ORDER BY RAND() 
            LIMIT ?
        ");
        // معالجة LIMIT في PDO
        $stmt->bindValue(1, (int)$topic_id, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($questions) < $limit) {
            // استكمال الأسئلة من نفس المستوى لضمان مسار كامل من 3-4 أسئلة
            $lvl_stmt = $pdo->prepare("SELECT level_id FROM topics WHERE id = ?");
            $lvl_stmt->execute([$topic_id]);
            $lvl_id = (int)$lvl_stmt->fetchColumn();

            $existing_ids = array_column($questions, 'id');
            $placeholders = !empty($existing_ids) ? implode(',', array_fill(0, count($existing_ids), '?')) : '0';
            
            $rem_stmt = $pdo->prepare("
                SELECT * FROM questions 
                WHERE level_id = ? AND id NOT IN ($placeholders) 
                ORDER BY RAND() 
                LIMIT ?
            ");
            $rem_stmt->bindValue(1, $lvl_id, PDO::PARAM_INT);
            $rem_stmt->bindValue(2, $limit - count($questions), PDO::PARAM_INT);
            $rem_stmt->execute();
            $more_q = $rem_stmt->fetchAll(PDO::FETCH_ASSOC);
            $questions = array_merge($questions, $more_q);
        }

        return $questions;
    }
}

if (!function_exists('calculate_user_skill_map')) {
    /**
     * حساب مصفوفة الكفاءات وخريطة المهارات (Skill Map) للطالب عبر المستويات الأربعة
     */
    function calculate_user_skill_map($pdo, $user_id) {
        $levels = $pdo->query("SELECT * FROM levels ORDER BY level_order ASC")->fetchAll(PDO::FETCH_ASSOC);
        $skill_map = [];

        foreach ($levels as $lvl) {
            $lvl_id = (int)$lvl['id'];

            // جلب أعلى درجة معتمدة في exam_results
            $best_res = $pdo->prepare("SELECT score, status FROM exam_results WHERE user_id = ? AND level_id = ? ORDER BY score DESC LIMIT 1");
            $best_res->execute([$user_id, $lvl_id]);
            $res = $best_res->fetch(PDO::FETCH_ASSOC);

            // جلب أحدث إحصائيات الأسئلة في هذا المستوى من جدول attempt_questions
            $q_stats = $pdo->prepare("
                SELECT 
                    COUNT(*) as total_answered,
                    SUM(CASE WHEN aq.is_correct = 1 THEN 1 ELSE 0 END) as correct_count
                FROM attempt_questions aq
                JOIN exam_attempts a ON aq.attempt_id = a.id
                JOIN questions q ON aq.question_id = q.id
                WHERE a.user_id = ? AND q.level_id = ?
            ");
            $q_stats->execute([$user_id, $lvl_id]);
            $stats = $q_stats->fetch(PDO::FETCH_ASSOC);

            $percentage = 0;
            if ($res && isset($res['score'])) {
                $percentage = (int)$res['score'];
            } elseif ($stats && $stats['total_answered'] > 0) {
                $percentage = round(($stats['correct_count'] / $stats['total_answered']) * 100);
            }

            // تصنيف رتبة المهارة
            if ($percentage >= 85) {
                $tier_title = 'خبير متمكن (Expert)';
                $tier_badge = 'badge-success';
                $tier_color = '#10B981';
                $tier_icon = 'fa-solid fa-crown';
            } elseif ($percentage >= 70) {
                $tier_title = 'متقدم (Advanced)';
                $tier_badge = 'badge-primary';
                $tier_color = '#5B4FE8';
                $tier_icon = 'fa-solid fa-certificate';
            } elseif ($percentage >= 50) {
                $tier_title = 'كفاءة متوسطة (Intermediate)';
                $tier_badge = 'badge-warning';
                $tier_color = '#F59E0B';
                $tier_icon = 'fa-solid fa-chart-line';
            } else {
                $tier_title = 'تأسيسي / قيد الدراسة (Foundational)';
                $tier_badge = 'badge-secondary';
                $tier_color = '#64748B';
                $tier_icon = 'fa-solid fa-seedling';
            }

            $skill_map[] = [
                'level_id' => $lvl_id,
                'level_name' => $lvl['level_name'],
                'level_order' => $lvl['level_order'],
                'percentage' => $percentage,
                'tier_title' => $tier_title,
                'tier_badge' => $tier_badge,
                'tier_color' => $tier_color,
                'tier_icon' => $tier_icon,
                'is_passed' => ($percentage >= 75)
            ];
        }

        return $skill_map;
    }
}

if (!function_exists('get_active_user_remediation')) {
    /**
     * جلب سجل المعالجة الدقيقة النشط للطالب
     */
    function get_active_user_remediation($pdo, $user_id) {
        $stmt = $pdo->prepare("
            SELECT * FROM remediation_records 
            WHERE user_id = ? AND remediation_status IN ('pending_choice', 'review_pending', 'side_quest_active') 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
