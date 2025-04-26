<?php

namespace Autocode;

class AcBackgroundFile {
    private string $filePath;
    private $process;
    private $pipes = [];

    public function __construct(string $filePath) {
        $this->filePath = $filePath;
        $this->startWriterProcess();
    }

    public function __destruct() {
        $this->close();
    }

    private function startWriterProcess(): static {
        $cmd = 'php ' . escapeshellarg(__DIR__ . '/AcBackgroundFileWorker.php') . ' ' . escapeshellarg($this->filePath);

        // Start the worker process
        $this->process = proc_open($cmd, [
            ['pipe', 'r'], // STDIN
            ['pipe', 'w'], // STDOUT
            ['pipe', 'w']  // STDERR
        ], $this->pipes);

        if (!is_resource($this->process)) {
            throw new \Exception("Failed to start writer process");
        }
        return $this;
    }

    public function writeAsString(string $content): static {
        if (is_resource($this->pipes[0])) {
            fwrite($this->pipes[0], $content . PHP_EOL);
            fflush($this->pipes[0]);
        }
        return $this;
    }

    public function close(): static {
        if (is_resource($this->pipes[0])) {
            fwrite($this->pipes[0], "exit\n"); // Signal worker to exit
            fflush($this->pipes[0]);
            fclose($this->pipes[0]);
        }

        if (is_resource($this->process)) {
            proc_terminate($this->process);
            proc_close($this->process);
        }
        return $this;
    }
}

?>
