<?php
/**
 * Shared Footer Component - منصة التعلم التكيفي الذكي للشبكات
 */
$isLoggedIn = isset($_SESSION['user_id']);
$footerBasePath = (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/admin/') !== false || strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/student/') !== false) ? '../' : './';
?>

<?php if ($isLoggedIn && !isset($examMode)): ?>
            </main><!-- .app-page-body -->

            <footer class="app-footer">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <img src="<?php echo $footerBasePath; ?>assets/img/icon.svg" alt="منصة التعلم التكيفي الذكي للشبكات" style="width:24px;height:24px;border-radius:7px;">
                    <span style="font-weight: 700; color: var(--text-heading);">منصة التعلم التكيفي الذكي للشبكات</span>
                    <span>&copy; <?php echo date('Y'); ?></span>
                </div>
                <div style="display: flex; gap: 16px; font-size: 0.82rem; color: var(--text-muted);">
                    <span><i class="fa-solid fa-network-wired" style="color: var(--brand-primary);"></i> نظام تدريب تكيفي ذكي</span>
                    <span><i class="fa-solid fa-certificate" style="color: var(--brand-secondary);"></i> معايير Cisco CCNA 200-301</span>
                    <span><i class="fa-solid fa-shield-halved" style="color: #10B981;"></i> مختبرات محاكاة سحابية</span>
                </div>
            </footer>
        </div><!-- .app-workspace -->
    </div><!-- .app-layout -->

<?php elseif (isset($examMode)): ?>
    <!-- وضع الامتحان: exam.php يغلق divs الخاصة به مباشرة -->

<?php else: ?>
    </main><!-- .landing-content -->

    <!-- ========================================================================= -->
    <!-- فوتر احترافي شامل للزوار                                                    -->
    <!-- ========================================================================= -->
    <footer style="background: linear-gradient(180deg, #1A1A3E 0%, #0F172A 100%); color: rgba(255,255,255,0.85); padding: 60px 28px 30px; margin-top: 0;">
        <div style="max-width: 1200px; margin: 0 auto;">
            
            <!-- القسم العلوي: 3 أعمدة -->
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 40px; margin-bottom: 40px; padding-bottom: 40px; border-bottom: 1px solid rgba(255,255,255,0.08);">
                
                <!-- عمود 1: عن المنصة -->
                <div>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                        <img src="<?php echo $footerBasePath; ?>assets/img/icon.svg" alt="منصة التعلم التكيفي الذكي للشبكات" style="width:40px;height:40px;border-radius:11px;box-shadow:0 4px 16px rgba(91,79,232,0.4);">
                        <div>
                            <strong style="color:#FFF;font-size:1.15rem;font-family:var(--font-heading);display:flex;align-items:center;gap:6px;">
                                منصة التعلم التكيفي الذكي للشبكات
                            </strong>
                            <div style="font-size:0.75rem;color:#818CF8;font-weight:600;margin-top:2px;">NetLearn Pro</div>
                        </div>
                    </div>
                    <p style="font-size:0.92rem;line-height:1.75;color:rgba(255,255,255,0.65);max-width:380px;margin-bottom:18px;">
                        بيئة تعليمية هندسية متقدمة وتكيفية تهدف إلى تأهيل الطلاب والمهندسين وفق أحدث المعايير الاحترافية لشهادة Cisco CCNA 200-301 ومحاكاة بيئات العمل الحقيقية.
                    </p>
                    <div style="display:flex;gap:12px;">
                        <div style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.5);font-size:0.9rem;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='rgba(91,79,232,0.3)';this.style.color='#FFF'" onmouseout="this.style.background='rgba(255,255,255,0.06)';this.style.color='rgba(255,255,255,0.5)'">
                            <i class="fa-brands fa-github"></i>
                        </div>
                        <div style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,0.5);font-size:0.9rem;cursor:pointer;transition:all 0.2s;" onmouseover="this.style.background='rgba(6,182,212,0.3)';this.style.color='#FFF'" onmouseout="this.style.background='rgba(255,255,255,0.06)';this.style.color='rgba(255,255,255,0.5)'">
                            <i class="fa-brands fa-linkedin-in"></i>
                        </div>
                    </div>
                </div>

                <!-- عمود 2: روابط سريعة -->
                <div>
                    <h4 style="color:#FFF;font-size:0.95rem;font-weight:700;margin-bottom:18px;display:flex;align-items:center;gap:6px;">
                        <i class="fa-solid fa-link" style="color:#06B6D4;font-size:0.8rem;"></i> روابط سريعة
                    </h4>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <a href="<?php echo $footerBasePath; ?>register.php" style="color:rgba(255,255,255,0.55);text-decoration:none;font-size:0.88rem;transition:color 0.2s;display:flex;align-items:center;gap:6px;" onmouseover="this.style.color='#A5B4FC'" onmouseout="this.style.color='rgba(255,255,255,0.55)'">
                            <i class="fa-solid fa-chevron-left" style="font-size:0.6rem;"></i> إنشاء حساب جديد
                        </a>
                        <a href="<?php echo $footerBasePath; ?>login.php" style="color:rgba(255,255,255,0.55);text-decoration:none;font-size:0.88rem;transition:color 0.2s;display:flex;align-items:center;gap:6px;" onmouseover="this.style.color='#A5B4FC'" onmouseout="this.style.color='rgba(255,255,255,0.55)'">
                            <i class="fa-solid fa-chevron-left" style="font-size:0.6rem;"></i> تسجيل الدخول
                        </a>
                        <a href="<?php echo $footerBasePath; ?>index.php#features" style="color:rgba(255,255,255,0.55);text-decoration:none;font-size:0.88rem;transition:color 0.2s;display:flex;align-items:center;gap:6px;" onmouseover="this.style.color='#A5B4FC'" onmouseout="this.style.color='rgba(255,255,255,0.55)'">
                            <i class="fa-solid fa-chevron-left" style="font-size:0.6rem;"></i> المختبرات التفاعلية
                        </a>
                        <a href="<?php echo $footerBasePath; ?>index.php#curriculum" style="color:rgba(255,255,255,0.55);text-decoration:none;font-size:0.88rem;transition:color 0.2s;display:flex;align-items:center;gap:6px;" onmouseover="this.style.color='#A5B4FC'" onmouseout="this.style.color='rgba(255,255,255,0.55)'">
                            <i class="fa-solid fa-chevron-left" style="font-size:0.6rem;"></i> المسار الأكاديمي
                        </a>
                    </div>
                </div>

                <!-- عمود 3: مسارات التدريب والمختبرات -->
                <div>
                    <h4 style="color:#FFF;font-size:0.95rem;font-weight:700;margin-bottom:18px;display:flex;align-items:center;gap:6px;">
                        <i class="fa-solid fa-flask" style="color:#10B981;font-size:0.8rem;"></i> المختبرات التفاعلية
                    </h4>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <span style="color:rgba(255,255,255,0.65);font-size:0.84rem;display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-terminal" style="color:#38BDF8;font-size:0.75rem;"></i> محاكي أوامر سيسكو CLI
                        </span>
                        <span style="color:rgba(255,255,255,0.65);font-size:0.84rem;display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-microchip" style="color:#34D399;font-size:0.75rem;"></i> محلل الحزم Wireshark
                        </span>
                        <span style="color:rgba(255,255,255,0.65);font-size:0.84rem;display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-network-wired" style="color:#F59E0B;font-size:0.75rem;"></i> مختبر Subnetting & VLSM
                        </span>
                        <span style="color:rgba(255,255,255,0.65);font-size:0.84rem;display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-diagram-project" style="color:#A78BFA;font-size:0.75rem;"></i> طوبولوجيا مسار الحزم الحي
                        </span>
                        <span style="color:rgba(255,255,255,0.65);font-size:0.84rem;display:flex;align-items:center;gap:6px;">
                            <i class="fa-solid fa-shield-halved" style="color:#F43F5E;font-size:0.75rem;"></i> أمن الشبكات والجدران النارية
                        </span>
                    </div>
                </div>
            </div>

            <!-- القسم السفلي -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div style="font-size: 0.84rem; color: rgba(255,255,255,0.45);">
                    جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?> — منصة التعلم التكيفي الذكي للشبكات (NetLearn Pro)
                </div>
                <div style="display:flex;align-items:center;gap:14px;font-size:0.8rem;color:rgba(255,255,255,0.45);">
                    <span style="display:flex;align-items:center;gap:4px;">
                        <i class="fa-solid fa-brain" style="color:rgba(91,79,232,0.8);"></i> نظام تعلم تكيفي ذكي
                    </span>
                    <span style="display:flex;align-items:center;gap:4px;">
                        <i class="fa-solid fa-certificate" style="color:rgba(6,182,212,0.8);"></i> معايير Cisco CCNA 200-301
                    </span>
                </div>
            </div>
        </div>
    </footer>
<?php endif; ?>

</body>
</html>
