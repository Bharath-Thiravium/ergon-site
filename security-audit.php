<?php
/**
 * Simple Security Audit Script for Ergon Task Management
 * Checks for common security vulnerabilities
 */

class SecurityAuditor
{
    private $issues = [];
    private $scannedFiles = 0;

    public function audit($directory = 'app/')
    {
        echo "🔍 Starting security audit...\n";
        $this->scanDirectory($directory);
        $this->generateReport();
    }

    private function scanDirectory($dir)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $this->scanFile($file->getPathname());
                $this->scannedFiles++;
            }
        }
    }

    private function scanFile($filepath)
    {
        $content = file_get_contents($filepath);
        
        // Check for SQL injection vulnerabilities
        if (preg_match('/\$_(?:GET|POST|REQUEST)\s*\[\s*[\'"][^\'"]*[\'"]\s*\]\s*[^;]*(?:SELECT|INSERT|UPDATE|DELETE)/i', $content)) {
            $this->addIssue('SQL Injection Risk', $filepath, 'Direct user input in SQL query');
        }

        // Check for XSS vulnerabilities
        if (preg_match('/echo\s+\$_(?:GET|POST|REQUEST)/i', $content)) {
            $this->addIssue('XSS Risk', $filepath, 'Unescaped user input in output');
        }

        // Check for file inclusion vulnerabilities
        if (preg_match('/(?:include|require)(?:_once)?\s*\(\s*\$_(?:GET|POST|REQUEST)/i', $content)) {
            $this->addIssue('File Inclusion Risk', $filepath, 'User input in file inclusion');
        }

        // Check for dangerous functions
        $dangerousFunctions = ['eval', 'exec', 'system', 'shell_exec', 'passthru'];
        foreach ($dangerousFunctions as $func) {
            if (preg_match('/\b' . $func . '\s*\(/i', $content)) {
                $this->addIssue('Dangerous Function', $filepath, "Use of $func() function");
            }
        }

        // Check for hardcoded credentials
        if (preg_match('/(?:password|pwd|secret|key)\s*=\s*[\'"][^\'"]{3,}[\'"]/i', $content)) {
            $this->addIssue('Hardcoded Credentials', $filepath, 'Potential hardcoded password/key');
        }
    }

    private function addIssue($type, $file, $description)
    {
        $this->issues[] = [
            'type' => $type,
            'file' => $file,
            'description' => $description,
            'severity' => $this->getSeverity($type)
        ];
    }

    private function getSeverity($type)
    {
        $severityMap = [
            'SQL Injection Risk' => 'HIGH',
            'XSS Risk' => 'HIGH',
            'File Inclusion Risk' => 'HIGH',
            'Dangerous Function' => 'MEDIUM',
            'Hardcoded Credentials' => 'MEDIUM'
        ];
        return $severityMap[$type] ?? 'LOW';
    }

    private function generateReport()
    {
        echo "\n📊 Security Audit Report\n";
        echo "========================\n";
        echo "Files scanned: {$this->scannedFiles}\n";
        echo "Issues found: " . count($this->issues) . "\n\n";

        if (empty($this->issues)) {
            echo "✅ No security issues detected!\n";
            return;
        }

        $severityCounts = array_count_values(array_column($this->issues, 'severity'));
        echo "Severity breakdown:\n";
        foreach (['HIGH', 'MEDIUM', 'LOW'] as $severity) {
            $count = $severityCounts[$severity] ?? 0;
            $icon = $severity === 'HIGH' ? '🔴' : ($severity === 'MEDIUM' ? '🟡' : '🟢');
            echo "  $icon $severity: $count\n";
        }

        echo "\nDetailed Issues:\n";
        echo "================\n";
        foreach ($this->issues as $issue) {
            $icon = $issue['severity'] === 'HIGH' ? '🔴' : ($issue['severity'] === 'MEDIUM' ? '🟡' : '🟢');
            echo "$icon [{$issue['severity']}] {$issue['type']}\n";
            echo "   File: {$issue['file']}\n";
            echo "   Issue: {$issue['description']}\n\n";
        }
    }
}

// Run the audit
if (php_sapi_name() === 'cli') {
    $auditor = new SecurityAuditor();
    $auditor->audit();
}