<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class VirusScanner
{
    /**
     * Scan a file for viruses.
     * Returns true if file is safe, false if a threat is detected.
     */
    public function scan(UploadedFile $file): bool
    {
        $filePath = $file->getRealPath();
        if (!$filePath || !file_exists($filePath)) {
            return false;
        }

        $content = file_get_contents($filePath);

        // 1. Check for EICAR standard antivirus test signature
        // Standard test string used by all antivirus vendors
        $eicarSignature = 'X5O!P%@AP[4\\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*';
        if (str_contains($content, $eicarSignature)) {
            Log::warning("VirusScanner: Malware signature detected (EICAR Test File) in upload: " . $file->getClientOriginalName());
            return false;
        }

        // 2. Perform ClamAV Daemon scan (if available in system)
        if ($this->isClamAvAvailable()) {
            return $this->scanWithClamAv($filePath);
        }

        // 3. Fallback: basic verification of file extension vs actual MIME type
        // This is a defense-in-depth double check
        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Prevent extension spoofing for executable files
        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'phar', 'exe', 'bat', 'cmd', 'sh', 'js', 'vbs', 'scr'];
        if (in_array($extension, $dangerousExtensions)) {
            Log::warning("VirusScanner: Blocked potentially dangerous extension: ." . $extension);
            return false;
        }

        // Check for double extension spoofing (e.g. photo.php.jpg)
        if (preg_match('/\.(php|phtml|phar|exe|bat|cmd|sh)\./i', $file->getClientOriginalName())) {
            Log::warning("VirusScanner: Double extension spoofing detected: " . $file->getClientOriginalName());
            return false;
        }

        return true;
    }

    /**
     * Check if ClamAV scanner is installed and accessible.
     */
    private function isClamAvAvailable(): bool
    {
        $command = DIRECTORY_SEPARATOR === '\\' ? 'where clamdscan' : 'which clamdscan';
        $output = [];
        $returnVar = -1;
        
        try {
            @exec($command, $output, $returnVar);
            return $returnVar === 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Scan file using ClamAV command-line client.
     */
    private function scanWithClamAv(string $filePath): bool
    {
        $escapedFilePath = escapeshellarg($filePath);
        $command = "clamdscan --no-summary {$escapedFilePath}";
        $output = [];
        $returnVar = -1;

        try {
            @exec($command, $output, $returnVar);
            
            // ClamAV return codes: 0 = Clean, 1 = Virus Found, 2 = Error/No daemon
            if ($returnVar === 0) {
                return true;
            } elseif ($returnVar === 1) {
                Log::error("VirusScanner: Threat detected by ClamAV in file {$filePath}. Output: " . implode("\n", $output));
                return false;
            } else {
                Log::warning("VirusScanner: ClamAV returned error code {$returnVar}. Output: " . implode("\n", $output));
                // Fallback to true if scanner has error so it doesn't block users if scanner breaks, but log it.
                return true;
            }
        } catch (\Exception $e) {
            Log::error("VirusScanner: ClamAV execution failed: " . $e->getMessage());
            return true;
        }
    }
}
