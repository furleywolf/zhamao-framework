<?php

/**
 * @noinspection PhpMissingReturnTypeInspection
 * @noinspection PhpUnused
 */

namespace ZM\MySQL;

use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\ForwardCompatibility\Result;
use Throwable;
use Traversable;
use ZM\Exception\DbException;

class MySQLStatementWrapper
{
    public $stmt;

    public function __construct(?Result $stmt) {
        $this->stmt = $stmt;
    }

    /**
     * 获取结果的迭代器
     * wrapper method
     * @return ResultStatement
     */
    public function getIterator() {
        return $this->stmt->getIterator();
    }

    /**
     * 获取列数
     * wrapper method
     * @return int
     */
    public function columnCount() {
        return $this->stmt->columnCount();
    }

    /**
     * wrapper method
     * @return array|false|mixed
     * @throws DbException
     */
    public function fetchNumeric() {
        try {
            return $this->stmt->fetchNumeric();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     * @return array|false|mixed
     * @throws DbException
     */
    public function fetchAssociative() {
        try {
            return $this->stmt->fetchAssociative();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     * @return false|mixed
     * @throws DbException
     */
    public function fetchOne() {
        try {
            return $this->stmt->fetchOne();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     * @return array
     * @throws DbException
     */
    public function fetchAllNumeric(): array {
        try {
            return $this->stmt->fetchAllNumeric();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     * @return array
     * @throws DbException
     */
    public function fetchAllAssociative(): array {
        try {
            return $this->stmt->fetchAllAssociative();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     * @return array
     * @throws DbException
     */
    public function fetchAllKeyValue(): array {
        try {
            return $this->stmt->fetchAllKeyValue();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     * @return array
     * @throws DbException
     */
    public function fetchAllAssociativeIndexed(): array {
        try {
            return $this->stmt->fetchAllAssociativeIndexed();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     * @return array
     * @throws DbException
     */
    public function fetchFirstColumn(): array {
        try {
            return $this->stmt->fetchFirstColumn();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     * @return Traversable
     * @throws DbException
     */
    public function iterateNumeric(): Traversable {
        try {
            return $this->stmt->iterateNumeric();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     * @return Traversable
     * @throws DbException
     */
    public function iterateAssociative(): Traversable {
        try {
            return $this->stmt->iterateAssociative();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     * @return Traversable
     * @throws DbException
     */
    public function iterateKeyValue(): Traversable {
        try {
            return $this->stmt->iterateKeyValue();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     * @return Traversable
     * @throws DbException
     */
    public function iterateAssociativeIndexed(): Traversable {
        try {
            return $this->stmt->iterateAssociativeIndexed();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     * @return Traversable
     * @throws DbException
     */
    public function iterateColumn(): Traversable {
        try {
            return $this->stmt->iterateColumn();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     * @return int
     * @throws DbException
     */
    public function rowCount() {
        try {
            return $this->stmt->rowCount();
        } catch (Throwable $e) {
            throw new DbException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * wrapper method
     */
    public function free(): void {
        $this->stmt->free();
    }


}