<?php
require_once '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['products'])) {
    $products = $_POST['products'];
    $count = 0;

    try {
        // トランザクション開始
        $pdo->beginTransaction();

        foreach ($products as $product) {
            if (empty($product['name']) || !is_numeric($product['price']) || !is_numeric($product['stock'])) continue;

            $name = $product['name'];
            $description = $product['description'] ?? '';
            $price = $product['price'];
            $category_id = $product['category_id'] ?? null;
            $stock = $product['stock'];
            $is_on_sale = isset($product['is_on_sale']) ? 1 : 0;
            $sale_price = $product['sale_price'] ?? null;
            $image = $product['image'] ?? null;
            $brand_id = $product['brand_id'] ?? null;
            $is_active = isset($product['is_active']) ? (int)$product['is_active'] : 1;

            $stmt = $pdo->prepare("INSERT INTO products 
                (name, description, price, image, category_id, stock, brand_id, is_on_sale, sale_price, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $image, $category_id, $stock, $brand_id, $is_on_sale, $sale_price, $is_active]);
            $count++;
        }

        // トランザクションをコミット
        $pdo->commit();

        // 成功メッセージをセッションに保存してリダイレクト
        session_start();
        $_SESSION['success_message'] = "{$count} 件の商品をフォームから登録しました。";
        header('Location: add_product.php');
        exit;

    } catch (PDOException $e) {
        // エラーが発生した場合はロールバック
        $pdo->rollBack();
        error_log('データベースエラー: ' . $e->getMessage());
        
        // エラーメッセージをセッションに保存してリダイレクト
        session_start();
        $_SESSION['error_message'] = "データベース登録中にエラーが発生しました。";
        header('Location: add_product.php');
        exit;
    }
} else {
    // 無効なデータの場合もリダイレクト
    session_start();
    $_SESSION['error_message'] = "無効なデータです。";
    header('Location: add_product.php');
    exit;
}
?>