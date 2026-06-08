<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Debug Thêm Category</h2>";

// Step 1: Check file tồn tại
echo "<h3>1. Kiểm tra file CategoryModel.php</h3>";
$modelPath = __DIR__ . '/../app/models/CategoryModel.php';
if (file_exists($modelPath)) {
    echo "✅ File tồn tại: $modelPath<br>";
    echo "Size: " . filesize($modelPath) . " bytes<br>";
} else {
    echo "❌ File KHÔNG tồn tại: $modelPath<br>";
    exit;
}

// Step 2: Load dependencies
echo "<h3>2. Load Database và Model</h3>";
try {
    require_once __DIR__ . '/../app/config/database.php';
    echo "✅ Database config loaded<br>";
    
    require_once $modelPath;
    echo "✅ CategoryModel loaded<br>";
    
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Database connected<br>";
    
    $model = new CategoryModel($db);
    echo "✅ CategoryModel instantiated<br>";
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Step 3: Test thêm category trực tiếp
echo "<h3>3. Test thêm category trực tiếp</h3>";
try {
    $testName = 'Test Category ' . time();
    $testDesc = 'Mô tả test';
    
    $result = $model->addCategory($testName, $testDesc);
    
    if ($result) {
        echo "✅ Thêm thành công!<br>";
        
        // Lấy ID vừa thêm
        $lastId = $db->lastInsertId();
        echo "ID vừa thêm: $lastId<br>";
        
        // Kiểm tra trong database
        $stmt = $db->query("SELECT * FROM category WHERE id = $lastId");
        $cat = $stmt->fetch(PDO::FETCH_OBJ);
        echo "<pre>";
        print_r($cat);
        echo "</pre>";
    } else {
        echo "❌ Thêm thất bại!<br>";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Step 4: Test API endpoint
echo "<h3>4. Test API endpoint</h3>";
echo "<p>Thử thêm category qua API:</p>";
echo "<button onclick='testAPI()'>Test POST /api/categories</button>";
echo "<div id='api-result' style='margin-top:10px;padding:10px;background:#f0f0f0;'></div>";
?>

<script>
function testAPI() {
    const data = {
        name: 'API Test ' + Date.now(),
        description: 'Test từ API'
    };
    
    fetch('/api/categories', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(res => {
        console.log('Status:', res.status);
        return res.text();
    })
    .then(text => {
        document.getElementById('api-result').innerHTML = '<pre>' + text + '</pre>';
    })
    .catch(err => {
        document.getElementById('api-result').innerHTML = '<pre style="color:red">Error: ' + err + '</pre>';
    });
}
</script>