<?php

class CentinelApiMySql extends Spatie\DbDumper\Databases\MySql
{
    public function getDumpCommand($dumpFile, $temporaryCredentialsFile)
    {
        $command = [
            "{$this->dumpBinaryPath}mysqldump",
            "--defaults-extra-file=\"{$temporaryCredentialsFile}\"",
            '--skip-comments',
            $this->useExtendedInserts ? '--extended-insert' : '--skip-extended-insert',
        ];

        if ($this->useSingleTransaction) {
            $command[] = '--single-transaction';
        }

        if ($this->socket != '') {
            $command[] = "--socket={$this->socket}";
        }

        if (!empty($this->excludeTables)) {
            $command[] = '--ignore-table=' . $this->dbName . '.' . implode(' --ignore-table=' . $this->dbName . '.', $this->excludeTables);
        }

        $command[] = "{$this->dbName}";

        if (!empty($this->includeTables)) {
            $command[] = implode(' ', $this->includeTables);
        }

        $command[] = "> \"{$dumpFile}\"";

        return implode(' ', $command);
    }
}
