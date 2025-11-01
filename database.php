<?php
/*
 * ============================================
 * BUYER OFFERS DATABASE HANDLER
 * ============================================
 *
 * Standalone SQLite database for the Offer Comparison Tool
 * All paths are relative for portability
 */

// Database file location (relative to this file)
define('DB_FILE', __DIR__ . '/data/offers.db');

/**
 * Get database connection
 */
function getDatabase() {
    // Create data directory if it doesn't exist
    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }

    try {
        $db = new PDO('sqlite:' . DB_FILE);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Initialize tables if they don't exist
        initializeTables($db);

        return $db;
    } catch (PDOException $e) {
        // Log error for debugging (in production, log to file instead)
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please contact the administrator.");
    }
}

/**
 * Initialize database tables
 */
function initializeTables($db) {
    // Create offer_comparisons table
    $db->exec("
        CREATE TABLE IF NOT EXISTS offer_comparisons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Create offers table
    $db->exec("
        CREATE TABLE IF NOT EXISTS offers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            comparison_id INTEGER NOT NULL,
            buyer_name TEXT NOT NULL,
            purchase_price REAL NOT NULL,
            down_payment REAL NOT NULL,
            seller_note_amount REAL NOT NULL,
            seller_note_rate REAL NOT NULL,
            seller_note_duration INTEGER NOT NULL,
            has_balloon INTEGER DEFAULT 0,
            balloon_year INTEGER DEFAULT 5,
            closing_date TEXT,
            contingencies TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (comparison_id) REFERENCES offer_comparisons(id) ON DELETE CASCADE
        )
    ");

    // Create index for faster queries
    $db->exec("CREATE INDEX IF NOT EXISTS idx_comparison_id ON offers(comparison_id)");
}

/**
 * Save a comparison and its offers
 */
function saveComparison($name, $offers) {
    $db = getDatabase();

    try {
        $db->beginTransaction();

        // Insert comparison
        $stmt = $db->prepare("INSERT INTO offer_comparisons (name) VALUES (?)");
        $stmt->execute([$name]);
        $comparisonId = $db->lastInsertId();

        // Insert all offers
        $stmt = $db->prepare("
            INSERT INTO offers (
                comparison_id, buyer_name, purchase_price, down_payment,
                seller_note_amount, seller_note_rate, seller_note_duration,
                has_balloon, balloon_year, contingencies
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($offers as $offer) {
            $stmt->execute([
                $comparisonId,
                $offer['buyer_name'],
                $offer['purchase_price'],
                $offer['down_payment'],
                $offer['seller_note_amount'],
                $offer['seller_note_rate'],
                $offer['seller_note_duration'],
                $offer['has_balloon'] ? 1 : 0,
                $offer['balloon_year'] ?? 5,
                $offer['contingencies'] ?? null
            ]);
        }

        $db->commit();
        return $comparisonId;

    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

/**
 * Load a comparison and its offers
 */
function loadComparison($comparisonId) {
    $db = getDatabase();

    // Get comparison info
    $stmt = $db->prepare("SELECT * FROM offer_comparisons WHERE id = ?");
    $stmt->execute([$comparisonId]);
    $comparison = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comparison) {
        return null;
    }

    // Get all offers for this comparison
    $stmt = $db->prepare("SELECT * FROM offers WHERE comparison_id = ? ORDER BY id");
    $stmt->execute([$comparisonId]);
    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert boolean fields
    foreach ($offers as &$offer) {
        $offer['has_balloon'] = (bool)$offer['has_balloon'];
    }

    return [
        'comparison' => $comparison,
        'offers' => $offers
    ];
}

/**
 * Get all saved comparisons
 */
function getAllComparisons() {
    $db = getDatabase();
    $stmt = $db->query("
        SELECT c.*, COUNT(o.id) as offer_count
        FROM offer_comparisons c
        LEFT JOIN offers o ON c.id = o.comparison_id
        GROUP BY c.id
        ORDER BY c.updated_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Delete a comparison and all its offers
 */
function deleteComparison($comparisonId) {
    $db = getDatabase();
    $stmt = $db->prepare("DELETE FROM offer_comparisons WHERE id = ?");
    return $stmt->execute([$comparisonId]);
}

/**
 * Update comparison timestamp
 */
function touchComparison($comparisonId) {
    $db = getDatabase();
    $stmt = $db->prepare("UPDATE offer_comparisons SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    return $stmt->execute([$comparisonId]);
}
