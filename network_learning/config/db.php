<?php
/**
 * Database Connection & Resilient Auto-Provisioning Engine
 * منصة التعلم التكيفي - الاتصال بقاعدة البيانات والإعداد الذاتي التلقائي
 */

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "network_adaptive_db";

try {
    // محاولة الاتصال المباشر بقاعدة البيانات
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // في حال عدم وجود قاعدة البيانات، الاتصال بخادم MySQL وإنشاؤها واستيراد المخطط تلقائياً
    try {
        $rootPdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true
        ]);
        $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        
        $sqlFile = __DIR__ . '/../database.sql';
        if (file_exists($sqlFile)) {
            $rootPdo->query("USE `{$dbname}`");
            $rootPdo->exec(file_get_contents($sqlFile));
        }

        // إعادة محاولة الاتصال بعد الإنشاء
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $ex) {
        die("<div style='font-family:sans-serif; direction:rtl; text-align:center; padding:40px; background:#f8fafc; color:#b91c1c; border:1px solid #fca5a5; margin:30px; border-radius:12px;'>
                <h2><i class='fa-solid fa-triangle-exclamation'></i> تعذر الاتصال بخادم قاعدة البيانات: {$dbname}</h2>
                <p>يرجى التأكد من تشغيل خادم MySQL في XAMPP / WAMP والتأكد من إعدادات الاتصال.</p>
                <p style='font-size:12px; color:#64748b; font-family:monospace;'>الخطأ: " . htmlspecialchars($ex->getMessage()) . "</p>
             </div>");
    }
}

// التأكد من توفر جميع الجداول والأعمدة

/**
 * وظيفة التحقق التلقائي من وجود وتوافق المخطط
 */
if (!function_exists('ensure_schema_upgrades')) {
function ensure_schema_upgrades($pdo) {
    try {
        // التأكد من وجود جدول levels وشحنه إن لزم
        $hasLevels = $pdo->query("SHOW TABLES LIKE 'levels'")->rowCount();
        if (!$hasLevels) {
            $sqlFile = __DIR__ . '/../database.sql';
            if (file_exists($sqlFile)) {
                $pdo->exec(file_get_contents($sqlFile));
                return;
            }
        }

        // فحص وتوسيع جدول users لدعم نظام التجاوز مع المعالجة الدقيقة
        $hasUsers = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount();
        if (!$hasUsers) {
            // جدول users غير موجود — لا يمكن تشغيل الترقيات بدونه
            error_log("Schema upgrade skipped: 'users' table not found in database.");
            return;
        }
        $userCols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('must_change_password', $userCols)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER current_level_id");
        }
        if (!in_array('remediation_status', $userCols)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN remediation_status VARCHAR(50) NOT NULL DEFAULT 'clear' AFTER current_level_id");
        }
        if (!in_array('highest_mastered_level_id', $userCols)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN highest_mastered_level_id INT NOT NULL DEFAULT 1 AFTER current_level_id");
        }

        // جدول المعالجة الدقيقة والتجاوز الذكي (Precision Remediation & Side Quests)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS remediation_records (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                source_attempt_id INT NULL,
                level_id INT NOT NULL,
                topic_id INT NOT NULL,
                topic_name VARCHAR(150) NOT NULL,
                concept_name VARCHAR(150) NOT NULL,
                initial_question_id INT NULL,
                verification_question_id INT NULL,
                verification_answer ENUM('A', 'B', 'C', 'D') NULL,
                is_verification_correct TINYINT(1) NULL,
                remediation_status ENUM('pending_choice', 'review_pending', 'resolved_slip', 'side_quest_active', 'side_quest_completed', 'opted_beginner') NOT NULL DEFAULT 'pending_choice',
                error_classification ENUM('unclassified', 'careless_mistake', 'knowledge_gap') NOT NULL DEFAULT 'unclassified',
                side_quest_score INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user_rem_status (user_id, remediation_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // التأكد من جدول exam_attempts
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS exam_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                level_id INT NULL,
                attempt_type ENUM('placement', 'level_exam', 'practice') NOT NULL DEFAULT 'level_exam',
                start_time DATETIME NOT NULL,
                end_time DATETIME NULL,
                max_duration_seconds INT NOT NULL DEFAULT 900,
                score INT NOT NULL DEFAULT 0,
                total_questions INT NOT NULL DEFAULT 0,
                correct_answers INT NOT NULL DEFAULT 0,
                passed TINYINT(1) NOT NULL DEFAULT 0,
                status ENUM('in_progress', 'completed', 'timed_out', 'abandoned') NOT NULL DEFAULT 'in_progress',
                attempt_token VARCHAR(64) NOT NULL UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_status (user_id, status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // التأكد من جدول attempt_questions
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS attempt_questions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                attempt_id INT NOT NULL,
                question_id INT NOT NULL,
                question_order INT NOT NULL DEFAULT 1,
                selected_option ENUM('A', 'B', 'C', 'D') NULL,
                is_correct TINYINT(1) NOT NULL DEFAULT 0,
                answered_at DATETIME NULL,
                UNIQUE KEY uk_att_q (attempt_id, question_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // التأكد من جدول lab_attempts
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS lab_attempts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                exercise_type VARCHAR(60) NOT NULL,
                target_ip VARCHAR(50) NOT NULL,
                cidr INT NOT NULL,
                user_answer TEXT NOT NULL,
                correct_answer TEXT NOT NULL,
                is_correct TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // التأكد من جدول learning_progress
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS learning_progress (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                module_id INT NOT NULL,
                is_completed TINYINT(1) NOT NULL DEFAULT 1,
                completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_user_module (user_id, module_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // ===================================================================
        // جدول المصادر والموارد التعليمية لكل مستوى (Learning Resources Hub)
        // ===================================================================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS level_resources (
                id INT AUTO_INCREMENT PRIMARY KEY,
                level_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT NULL,
                resource_type ENUM('video','pdf','article','interactive','quiz','audio') NOT NULL DEFAULT 'video',
                resource_lang ENUM('ar','en','mixed') NOT NULL DEFAULT 'ar',
                url TEXT NOT NULL,
                is_external TINYINT(1) NOT NULL DEFAULT 1,
                duration_label VARCHAR(50) NULL COMMENT 'e.g. 12 دقيقة أو 45 صفحة',
                thumbnail_url TEXT NULL,
                author_name VARCHAR(150) NULL,
                sort_order INT NOT NULL DEFAULT 0,
                is_featured TINYINT(1) NOT NULL DEFAULT 0,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                view_count INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_level_active (level_id, is_active),
                INDEX idx_level_type (level_id, resource_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // جدول تتبع تفاعل الطالب مع الموارد (Resource Engagement Tracker)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS resource_views (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                resource_id INT NOT NULL,
                viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_completed TINYINT(1) NOT NULL DEFAULT 0,
                UNIQUE KEY uk_user_resource (user_id, resource_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // ===================================================================
        // جدول الدروس النصية (مقالات وشروحات تُقرأ داخل المنصة)
        // ===================================================================
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS level_lessons (
                id INT AUTO_INCREMENT PRIMARY KEY,
                level_id INT NOT NULL,
                title VARCHAR(300) NOT NULL,
                summary TEXT,
                content LONGTEXT NOT NULL,
                estimated_read_minutes INT DEFAULT 10,
                is_active TINYINT(1) DEFAULT 1,
                is_featured TINYINT(1) DEFAULT 0,
                sort_order INT DEFAULT 0,
                author_name VARCHAR(150) DEFAULT 'فريق المنصة',
                view_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_ll_level (level_id, is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        // جدول تتبع قراءة الدروس النصية
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS lesson_reads (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                lesson_id INT NOT NULL,
                read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_completed TINYINT(1) DEFAULT 0,
                read_percentage INT DEFAULT 0,
                UNIQUE KEY uk_user_lesson (user_id, lesson_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

    } catch (Exception $e) {
        // تسجيل الخطأ بهدوء إن وجد دون تعطيل الخدمة
        error_log("Schema ensure error: " . $e->getMessage());
    }
}
}
ensure_schema_upgrades($pdo);
?>