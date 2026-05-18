<?php
require_once('app/config/database.php');
require_once('app/models/CategoryModel.php');

class CategoryController
{
    private $categoryModel;
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->categoryModel = new CategoryModel($this->db);
    }

    // Danh sách categories
    public function list()
    {
        $categories = $this->categoryModel->getCategories();
        include 'app/views/category/list.php';
    }

    // Hiển thị form thêm category
    public function add()
    {
        include 'app/views/category/add.php';
    }

    // Xử lý thêm category
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];

            if ($this->categoryModel->create($name, $description)) {
                header('Location: /Category/list?success=1');
                exit();
            } else {
                $error = "Thêm category thất bại";
                include 'app/views/category/add.php';
            }
        }
    }

    // Hiển thị form sửa category
    public function edit($id)
    {
        $category = $this->categoryModel->getCategoryById($id);
        if (!$category) {
            header('Location: /Category/list?error=notfound');
            exit();
        }
        include 'app/views/category/edit.php';
    }

    // Xử lý cập nhật category
    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];

            if ($this->categoryModel->update($id, $name, $description)) {
                header('Location: /Category/list?success=1');
                exit();
            } else {
                $error = "Cập nhật category thất bại";
                $category = (object)['id' => $id, 'name' => $name, 'description' => $description];
                include 'app/views/category/edit.php';
            }
        }
    }

    // Xóa category
    public function delete($id)
    {
        if ($this->categoryModel->delete($id)) {
            header('Location: /Category/list?success=1');
            exit();
        } else {
            header('Location: /Category/list?error=1');
            exit();
        }
    }
}
?>