            </main>
            <!-- Main Content End -->
        </div>
    </div>

    <!-- jQuery must be loaded before Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Keep if you plan to use charts on other pages -->
    <script src="<?php echo rtrim(dirname($_SERVER['PHP_SELF']), '/\\') ?: ''; ?>/scripts.js"></script> <!-- Dynamic path for scripts.js -->
</body>
</html>
<?php
// Close database connection if it's open and $link is a valid resource
if (isset($link) && is_resource($link) && get_resource_type($link) === 'mysql link') {
    mysqli_close($link);
}
?>
