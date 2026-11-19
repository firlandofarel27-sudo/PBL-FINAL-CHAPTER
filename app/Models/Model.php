<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Basis model: satu pintu ke PDO dengan prepared statement.
 *
 * Semua query memakai parameter terikat sehingga tidak ada nilai pengguna
 * yang disisipkan langsung ke SQL.
 */
abstract class Model
{
    protected static function pdo(): PDO
    {
        return Database::pdo();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function rows(string $sql, array $params = []): array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    protected static function row(string $sql, array $params = []): ?array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);

        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Query agregat lintas tabel yang tidak dimiliki satu model tertentu.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function query(string $sql, array $params = []): array
    {
        return self::rows($sql, $params);
    }

    protected static function scalar(string $sql, array $params = []): mixed
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    protected static function execute(string $sql, array $params = []): int
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /**
     * Menyusun klausa WHERE dinamis dari potongan yang tidak kosong.
     *
     * @param  array<int, string>  $conditions
     */
    protected static function where(array $conditions): string
    {
        $conditions = array_values(array_filter($conditions));

        return $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
    }
}
