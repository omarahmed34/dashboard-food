<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

// ============================================================
// GET REQUESTS
// ============================================================
if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    // ── All Dashboard Data ───────────────────────────────────
    if ($action === 'getAllData') {
        try {
            // Recipes
            $stmt = $pdo->query("SELECT * FROM recipes ORDER BY id DESC");
            $recipes = $stmt->fetchAll();

            $stmt = $pdo->query("SELECT recipe_id, ingredient_id FROM recipe_ingredients");
            $recipeIngredients = $stmt->fetchAll();
            $riMap = [];
            foreach ($recipeIngredients as $ri) {
                $riMap[$ri['recipe_id']][] = $ri['ingredient_id'];
            }

            $formattedRecipes = array_map(function ($r) use ($riMap) {
                $steps = [];
                if (!empty($r['steps'])) {
                    $decoded = json_decode($r['steps'], true);
                    $steps = (json_last_error() === JSON_ERROR_NONE)
                        ? $decoded
                        : array_filter(array_map('trim', explode("\n", $r['steps'])));
                }
                return [
                    'id' => (int) $r['id'],
                    'name' => $r['name'] ?? '',
                    'category' => $r['category'] ?? '',
                    'time' => $r['time'] ?? '',
                    'difficulty' => $r['difficulty'] ?? '',
                    'status' => $r['status'] ?? '',
                    'image' => $r['image'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80',
                    'ingredients' => $riMap[$r['id']] ?? [],
                    'steps' => $steps,
                    'saves' => (int) ($r['saves'] ?? 0),
                    'views' => (int) ($r['views'] ?? 0)
                ];
            }, $recipes);

            // Ingredients
            $stmt = $pdo->query("SELECT * FROM ingredients");
            $ingredients = $stmt->fetchAll();
            $formattedIngredients = array_map(function ($i) {
                return [
                    'id' => $i['id'] ?? 0,
                    'name' => $i['name'] ?? '',
                    'emoji' => $i['emoji'] ?? '',
                    'category' => $i['category'] ?? '',
                    'usedIn' => (int) ($i['used_in'] ?? 0)
                ];
            }, $ingredients);

            // Users
            $stmt = $pdo->query("SELECT * FROM users");
            $users = $stmt->fetchAll();
            $formattedUsers = array_map(function ($u) {
                return [
                    'id' => (int) $u['id'],
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'avatar' => $u['avatar'],
                    'joinDate' => $u['join_date'],
                    'saves' => (int) ($u['saves'] ?? 0),
                    'status' => $u['status']
                ];
            }, $users);

            // Favorites
            $stmt = $pdo->query("SELECT * FROM favorites");
            $favorites = $stmt->fetchAll();
            $formattedFavorites = array_map(function ($f) {
                return [
                    'recipeId' => (int) $f['recipe_id'],
                    'userId' => (int) $f['user_id'],
                    'date' => $f['date']
                ];
            }, $favorites);

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'recipes' => $formattedRecipes,
                    'ingredients' => $formattedIngredients,
                    'users' => $formattedUsers,
                    'favorites' => $formattedFavorites
                ]
            ]);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ── Dash Login Users ─────────────────────────────────────
    if ($action === 'getDashUsers') {
        try {
            $stmt = $pdo->query("SELECT id, name, email, password, role, created_at FROM dashlogen ORDER BY id DESC");
            $rows = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ── Contact Messages ─────────────────────────────────────
    if ($action === 'getContacts') {
        try {
            $stmt = $pdo->query("SELECT * FROM contact ORDER BY id DESC");
            $rows = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ── Login Activity (Sine) ─────────────────────────────────
    if ($action === 'getSineUsers') {
        try {
            $stmt = $pdo->query("SELECT * FROM sine ORDER BY id DESC");
            $rows = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    if ($action === 'getAboutContent') {
        try {
            // Create flat table if missing
            $pdo->exec("CREATE TABLE IF NOT EXISTS about_page (
                id INT AUTO_INCREMENT PRIMARY KEY,
                lang ENUM('ar', 'en') NOT NULL UNIQUE,
                badge VARCHAR(255),
                title VARCHAR(255),
                vision_title VARCHAR(255),
                vision_text TEXT,
                mission_title VARCHAR(255),
                mission_text TEXT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $stmt = $pdo->query("SELECT * FROM about_page");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    if ($action === 'getOrders') {
        try {
            // Auto-create orders table if missing
            $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT DEFAULT NULL,
                recipe_id INT DEFAULT NULL,
                full_name VARCHAR(255) NOT NULL,
                emall VARCHAR(255) DEFAULT NULL,
                phone VARCHAR(50) DEFAULT NULL,
                address TEXT DEFAULT NULL,
                delivery_date VARCHAR(50) DEFAULT NULL,
                delivery_time VARCHAR(50) DEFAULT NULL,
                location_link TEXT DEFAULT NULL,
                status VARCHAR(50) DEFAULT 'Pending',
                order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $stmt = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC");
            $rows = $stmt->fetchAll();
            echo json_encode(['status' => 'success', 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

// ============================================================
// POST REQUESTS
// ============================================================
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? ($input['action'] ?? '');

    // ── Save Recipe ──────────────────────────────────────────
    if ($action === 'saveRecipe') {
        try {
            $pdo->beginTransaction();

            $name = $input['name'];
            $category = $input['category'];
            $time = $input['time'];
            $difficulty = $input['difficulty'];
            $status = $input['status'];
            $image = $input['image']; // Can be URL or Base64
            $steps = json_encode($input['steps'], JSON_UNESCAPED_UNICODE);
            $ingredients = $input['ingredients'] ?? [];
            $saves = $input['saves'] ?? 0;
            $views = $input['views'] ?? 0;

            // Handle Base64 Image if sent directly
            if (strpos($image, 'data:image') === 0) {
                $uploadDir = 'uploads/';
                if (!is_dir($uploadDir))
                    mkdir($uploadDir, 0777, true);

                $data = explode(',', $image);
                $ext = 'png';
                if (strpos($data[0], 'jpeg') !== false)
                    $ext = 'jpg';
                if (strpos($data[0], 'webp') !== false)
                    $ext = 'webp';

                $fileName = uniqid('img_') . '.' . $ext;
                file_put_contents($uploadDir . $fileName, base64_decode($data[1]));

                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                $scriptPath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
                $image = $protocol . "://" . $_SERVER['HTTP_HOST'] . $scriptPath . '/' . $uploadDir . $fileName;
            }

            if (isset($input['id']) && !empty($input['id'])) {
                $id = $input['id'];
                $pdo->prepare("UPDATE `recipes` SET `name`=?, `category`=?, `time`=?, `difficulty`=?, `status`=?, `image`=?, `steps`=? WHERE `id`=?")
                    ->execute([$name, $category, $time, $difficulty, $status, $image, $steps, $id]);
                $pdo->prepare("DELETE FROM `recipe_ingredients` WHERE `recipe_id`=?")->execute([$id]);
            } else {
                $pdo->prepare("INSERT INTO `recipes` (`name`, `category`, `time`, `difficulty`, `status`, `image`, `steps`, `saves`, `views`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$name, $category, $time, $difficulty, $status, $image, $steps, $saves, $views]);
                $id = $pdo->lastInsertId();
            }

            $stmt_ri = $pdo->prepare("INSERT INTO `recipe_ingredients` (`recipe_id`, `ingredient_id`) VALUES (?, ?)");
            foreach ($ingredients as $ing_id) {
                $stmt_ri->execute([$id, $ing_id]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Recipe saved successfully', 'image' => $image]);

        } catch (Exception $e) {
            if ($pdo->inTransaction())
                $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ── Delete Recipe ────────────────────────────────────────
    elseif ($action === 'deleteRecipe') {
        try {
            $id = $input['id'];
            $pdo->prepare("DELETE FROM recipe_ingredients WHERE recipe_id=?")->execute([$id]);
            $pdo->prepare("DELETE FROM recipes WHERE id=?")->execute([$id]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ── Save Ingredient ──────────────────────────────────────
    elseif ($action === 'saveIngredient') {
        try {
            $id = $input['id'] ?? null;
            $name = $input['name'];
            $emoji = $input['emoji'];
            $category = $input['category'];
            $used_in = $input['usedIn'] ?? 0;

            if (!empty($id) && is_numeric($id)) {
                $stmt = $pdo->prepare("SELECT id FROM ingredients WHERE id=?");
                $stmt->execute([$id]);
                if ($stmt->fetch()) {
                    $pdo->prepare("UPDATE ingredients SET name=?, emoji=?, category=?, used_in=? WHERE id=?")
                        ->execute([$name, $emoji, $category, $used_in, $id]);
                    echo json_encode(['status' => 'success']);
                    exit;
                }
            }
            $pdo->prepare("INSERT INTO ingredients (name, emoji, category, used_in) VALUES (?, ?, ?, ?)")
                ->execute([$name, $emoji, $category, $used_in]);
            echo json_encode(['status' => 'success']);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ── Delete Ingredient ────────────────────────────────────
    elseif ($action === 'deleteIngredient') {
        try {
            $id = $input['id'];
            $pdo->prepare("DELETE FROM recipe_ingredients WHERE ingredient_id=?")->execute([$id]);
            $pdo->prepare("DELETE FROM ingredients WHERE id=?")->execute([$id]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ── Save App User ────────────────────────────────────────
    elseif ($action === 'saveUser') {
        try {
            $name = $input['name'];
            $email = $input['email'];
            $avatar = $input['avatar'];
            $join_date = $input['joinDate'] ?? date('Y-m-d');
            $saves = $input['saves'] ?? 0;
            $status = $input['status'] ?? 'نشط';

            // Check if user exists by email, if so UPDATE, else INSERT
            $stmt = $pdo->prepare("INSERT INTO users (name, email, avatar, join_date, saves, status) 
                                 VALUES (?, ?, ?, ?, ?, ?) 
                                 ON DUPLICATE KEY UPDATE 
                                 name = VALUES(name), 
                                 avatar = VALUES(avatar), 
                                 status = VALUES(status)");
            $stmt->execute([$name, $email, $avatar, $join_date, $saves, $status]);

            echo json_encode(['status' => 'success', 'message' => 'تم حفظ بيانات المستخدم بنجاح']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => "خطأ في قاعدة البيانات: " . $e->getMessage()]);
        }
    }

    // ── Delete App User ──────────────────────────────────────
    elseif ($action === 'deleteUser') {
        try {
            $id = $input['id'];
            $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ── Save Dash Login User ─────────────────────────────────
    elseif ($action === 'saveDashUser') {
        try {
            $id = $input['id'] ?? null;
            $name = trim($input['name'] ?? '');
            $email = trim($input['email'] ?? '');
            $password = trim($input['password'] ?? '');
            $role = $input['role'] ?? 'editor';

            if (empty($name) || empty($email)) {
                echo json_encode(['status' => 'error', 'message' => 'الاسم والبريد الإلكتروني مطلوبان']);
                exit;
            }

            if ($id) {
                // Update
                // Check if email taken by someone else
                $chk = $pdo->prepare("SELECT id FROM dashlogen WHERE email=? AND id!=?");
                $chk->execute([$email, $id]);
                if ($chk->fetch()) {
                    echo json_encode(['status' => 'error', 'message' => 'هذا البريد الإلكتروني مستخدم بالفعل من قبل شخص آخر']);
                    exit;
                }

                if (!empty($password)) {
                    $pdo->prepare("UPDATE dashlogen SET name=?, email=?, password=?, role=? WHERE id=?")
                        ->execute([$name, $email, $password, $role, $id]);
                } else {
                    $pdo->prepare("UPDATE dashlogen SET name=?, email=?, role=? WHERE id=?")
                        ->execute([$name, $email, $role, $id]);
                }
                echo json_encode(['status' => 'success', 'message' => 'تم تعديل الحساب بنجاح']);
            } else {
                // Insert
                // EXPLICIT CHECK
                $chk = $pdo->prepare("SELECT name FROM dashlogen WHERE email=?");
                $chk->execute([$email]);
                $existing = $chk->fetch();

                if ($existing) {
                    echo json_encode(['status' => 'error', 'message' => 'خطأ: هذا البريد الإلكتروني مسجل مسبقاً باسم: ' . $existing['name']]);
                    exit;
                }

                if (empty($password)) {
                    echo json_encode(['status' => 'error', 'message' => 'كلمة المرور مطلوبة عند الإضافة']);
                    exit;
                }

                $username = uniqid('user_') . rand(100, 999);

                try {
                    $pdo->prepare("INSERT INTO dashlogen (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)")
                        ->execute([$name, $username, $email, $password, $role]);
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Unknown column') !== false) {
                        $pdo->prepare("INSERT INTO dashlogen (name, email, password, role) VALUES (?, ?, ?, ?)")
                            ->execute([$name, $email, $password, $role]);
                    } else {
                        throw $e;
                    }
                }

                echo json_encode(['status' => 'success', 'message' => 'تمت إضافة الحساب بنجاح. كلمة السر: ' . $password]);
            }

        } catch (Exception $e) {
            $errCode = $e->getCode();
            $errMsg = $e->getMessage();

            // Duplicate email or integrity constraint
            if ($errCode == 23000) {
                // Try to find who has this email again for the error message
                $chk = $pdo->prepare("SELECT name, email FROM dashlogen WHERE email=?");
                $chk->execute([$email]);
                $existing = $chk->fetch();

                if ($existing) {
                    echo json_encode(['status' => 'error', 'message' => 'خطأ: البريد الإلكتروني (' . $existing['email'] . ') مسجل مسبقاً باسم: ' . $existing['name']]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'قيد في قاعدة البيانات: ' . $errMsg]);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'خطأ نظام: ' . $errMsg]);
            }
        }
    }

    // ── Delete Dash Login User ───────────────────────────────
    elseif ($action === 'deleteDashUser') {
        try {
            $id = (int) ($input['id'] ?? 0);
            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'ID غير صالح']);
                exit;
            }
            // Prevent deleting last admin
            $count = $pdo->query("SELECT COUNT(*) FROM dashlogen WHERE role='admin'")->fetchColumn();
            $user = $pdo->prepare("SELECT role FROM dashlogen WHERE id=?");
            $user->execute([$id]);
            $row = $user->fetch();
            if ($count <= 1 && $row && $row['role'] === 'admin') {
                echo json_encode(['status' => 'error', 'message' => 'لا يمكن حذف آخر مدير في النظام']);
                exit;
            }
            $pdo->prepare("DELETE FROM dashlogen WHERE id=?")->execute([$id]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    // ── Upload Image ──────────────────────────────────────────
    elseif ($action === 'uploadImage') {
        try {
            if (!isset($_FILES['image'])) {
                echo json_encode(['status' => 'error', 'message' => 'لم يتم اختيار صورة']);
                exit;
            }

            $file = $_FILES['image'];
            $uploadDir = 'uploads/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) {
                echo json_encode(['status' => 'error', 'message' => 'نوع الملف غير مدعوم']);
                exit;
            }

            $fileName = uniqid('img_') . '.' . $ext;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Return a relative path to avoid "localhost" issues when moving the project
                // The frontend will prepend the server root if needed, or we return the full URL correctly
                $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                $host = $_SERVER['HTTP_HOST'];
                $scriptPath = dirname($_SERVER['PHP_SELF']);
                $cleanPath = rtrim(str_replace('\\', '/', $scriptPath), '/');
                $fullUrl = $protocol . "://" . $host . $cleanPath . '/' . $targetPath;

                echo json_encode(['status' => 'success', 'url' => $fullUrl]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'فشل نقل الملف المرفوع للجناح ' . $uploadDir]);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    // ── Update About Page Content (Flat Structure) ─────────────
    elseif ($action === 'updateAboutContent') {
        try {
            if (!isset($input['contents']) || !is_array($input['contents'])) {
                die(json_encode(['status' => 'error', 'message' => "بيانات غير مكتملة"]));
            }

            $pdo->beginTransaction();
            foreach ($input['contents'] as $lang => $data) {
                $stmt = $pdo->prepare("INSERT INTO about_page (lang, badge, title, vision_title, vision_text, mission_title, mission_text) 
                                     VALUES (:lang, :badge, :title, :v_title, :v_text, :m_title, :m_text) 
                                     ON DUPLICATE KEY UPDATE 
                                        badge = VALUES(badge), 
                                        title = VALUES(title), 
                                        vision_title = VALUES(vision_title), 
                                        vision_text = VALUES(vision_text), 
                                        mission_title = VALUES(mission_title), 
                                        mission_text = VALUES(mission_text)");
                $stmt->execute([
                    ':lang' => $lang,
                    ':badge' => $data['badge'] ?? '',
                    ':title' => $data['title'] ?? '',
                    ':v_title' => $data['vision_title'] ?? '',
                    ':v_text' => $data['vision_text'] ?? '',
                    ':m_title' => $data['mission_title'] ?? '',
                    ':m_text' => $data['mission_text'] ?? ''
                ]);
            }
            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction())
                $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => "خطأ في القاعدة: " . $e->getMessage()]);
        }
    }
    // ── Update Order Status ────────────────────────────────────
    elseif ($action === 'updateOrderStatus') {
        try {
            $id = $input['id'];
            $status = $input['status'];
            $pdo->prepare("UPDATE orders SET status=? WHERE id=?")
                ->execute([$status, $id]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ── Delete Order ──────────────────────────────────────────
    elseif ($action === 'deleteOrder') {
        try {
            $id = $input['id'];
            $pdo->prepare("DELETE FROM orders WHERE id=?")->execute([$id]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ── Save Order (Place New Order) ──────────────────────────
    elseif ($action === 'saveOrder') {
        try {
            $user_id = $input['user_id'] ?? null;
            $recipe_id = $input['recipe_id'] ?? null;
            $full_name = $input['full_name'];
            $emall = $input['emall'] ?? $input['email'];
            $phone = $input['phone'];
            $address = $input['address'];
            $delivery_date = $input['delivery_date'];
            $delivery_time = $input['delivery_time'];
            $location_link = $input['location_link'] ?? null;
            $status = $input['status'] ?? 'Pending';

            $pdo->prepare("INSERT INTO orders (user_id, recipe_id, full_name, emall, phone, address, delivery_date, delivery_time, location_link, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                ->execute([$user_id, $recipe_id, $full_name, $emall, $phone, $address, $delivery_date, $delivery_time, $location_link, $status]);

            echo json_encode(['status' => 'success', 'order_id' => $pdo->lastInsertId()]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
?>