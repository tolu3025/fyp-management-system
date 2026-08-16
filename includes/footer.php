<?php
// includes/footer.php
// Standard HTML footer and the Developer Email Log simulator terminal drawer

$log_file = __DIR__ . '/../logs/emails.log';
$email_logs = '';
if (file_exists($log_file)) {
    $email_logs = file_get_contents($log_file);
}
?>
            </main>
            <!-- Content Area end -->

            <!-- Developers Simulated Email Log Console Drawer -->
            <footer class="dev-mail-console">
                <div class="dev-mail-header" id="devMailHeader">
                    <span>🛠️ Developer Email Logs Console (Local Environment Simulator)</span>
                    <span class="toggle-indicator">▲</span>
                </div>
                <div class="dev-mail-content" id="devMailContent">
                    <?php if (empty($email_logs)): ?>
                        <div class="dev-mail-record">No emails dispatched yet. Activating notification triggers will simulate SMTP traffic here.</div>
                    <?php else: ?>
                        <!-- Present email logs in reverse order (newest first) -->
                        <?php
                        $records = explode("=================================================================", $email_logs);
                        $records = array_filter(array_map('trim', $records));
                        $records = array_reverse($records);
                        foreach ($records as $record):
                        ?>
                            <div class="dev-mail-record">
                                <?= nl2br(sanitize($record)) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Custom Application Javascript -->
    <script src="assets/js/app.js"></script>
</body>
</html>
