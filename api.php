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

            $formattedRecipes = array_map(function($r) use ($riMap) {
                $steps = [];
                if (!empty($r['steps'])) {
                    $decoded = json_decode($r['steps'], true);
                    $steps = (json_last_error() === JSON_ERROR_NONE)
                        ? $decoded
                        : array_filter(array_map('trim', explode("\n", $r['steps'])));
                }
                return [
                    'id'          => (int)$r['id'],
                    'name'        => $r['name'] ?? '',
                    'category'    => $r['category'] ?? '',
                    'time'        => $r['time'] ?? '',
                    'difficulty'  => $r['difficulty'] ?? '',
                    'status'      => $r['status'] ?? '',
                    'image'       => $r['image'] ?? 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80',
                    'ingredients' => $riMap[$r['id']] ?? [],
                    'steps'       => $steps,
                    'saves'       => (int)($r['saves'] ?? 0),
                    'views'       => (int)($r['views'] ?? 0)
                ];
            }, $recipes);

            // Ingredients
            $stmt = $pdo->query("SELECT * FROM ingredients");
            $ingredients = $stmt->fetchAll();
            $formattedIngredients = array_map(function($i) {
                return [
                    'id'       => $i['id'],
                    'name'     => $i['name'],
                    'emoji'    => $i['emoji'],
                    'category' => $i['category'],
                    'usedIn'   => (int)($i['used_in'] ?? 0)
                ];
            }, $ingredients);

            // Users
            $stmt = $pdo->query("SELECT * FROM users");
            $users = $stmt->fetchAll();
            $formattedUsers = array_map(function($u) {
                return [
                    'id'       => (int)$u['id'],
                    'name'     => $u['name'],
                    'email'    => $u['email'],
                    'avatar'   => $u['avatar'],
                    'joinDate' => $u['join_date'],
                    'saves'    => (int)($u['saves'] ?? 0),
                    'status'   => $u['status']
                ];
            }, $users);

            // Favorites
            $stmt = $pdo->query("SELECT * FROM favorites");
            $favorites = $stmt->fetchAll();
            $formattedFavorites = array_map(function($f) {
                return [
                    'recipeId' => (int)$f['recipe_id'],
                    'userId'   => (int)$f['user_id'],
                    'date'     => $f['date']
                ];
            }, $favorites);

            echo json_encode([
                'status' => 'success',
                'data'   => [
                    'recipes'     => $formattedRecipes,
                    'ingredients' => $formattedIngredients,
                    'users'       => $formattedUsers,
                    'favorites'   => $formattedFavorites
                ]
            ]);

        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // ── Dash Login Users ─────────────────────────────────────
    if ($action === 'getDashUsers') {
        try {
            $stmt = $pdo->query("SELECT id, name, email, role, created_at FROM dashlogen ORDER BY id DESC");
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
}

// ============================================================
// POST REQUESTS
// ============================================================
if ($method === 'POST') {
    $input  = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? ($input['action'] ?? '');

    // ── Save Recipe ──────────────────────────────────────────
    if ($action === 'saveRecipe') {
        try {
            $pdo->beginTransaction();

            $name        = $input['name'];
            $category    = $input['category'];
            $time        = $input['time'];
            $difficulty  = $input['difficulty'];
            $status      = $input['status'];
            $image       = $input['image'];
            $steps       = json_encode($input['steps'], JSON_UNESCAPED_UNICODE);
            $ingredients = $input['ingredients'] ?? [];
            $saves       = $input['saves'] ?? 0;
            $views       = $input['views'] ?? 0;

            if (isset($input['id']) && !empty($input['id'])) {
                $id = $input['id'];
                $pdo->prepare("UPDATE recipes SET name=?, category=?, time=?, difficulty=?, status=?, image=?, steps=? WHERE id=?")
                    ->execute([$name, $category, $time, $difficulty, $status, $image, $steps, $id]);
                $pdo->prepare("DELETE FROM recipe_ingredients WHERE recipe_id=?")->execute([$id]);
            } else {
                $pdo->prepare("INSERT INTO recipes (name, category, time, difficulty, status, image, steps, saves, views) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
                    ->execute([$name, $category, $time, $difficulty, $status, $image, $steps, $saves, $views]);
                $id = $pdo->lastInsertId();
            }

            $stmt_ri = $pdo->prepare("INSERT INTO recipe_ingredients (recipe_id, ingredient_id) VALUES (?, ?)");
            foreach ($ingredients as $ing_id) {
                $stmt_ri->execute([$id, $ing_id]);
            }

            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Recipe saved']);

        } catch (Exception $e) {
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
            $id       = $input['id'] ?? null;
            $name     = $input['name'];
            $emoji    = $input['emoji'];
            $category = $input['category'];
            $used_in  = $input['usedIn'] ?? 0;

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
            $name      = $input['name'];
            $email     = $input['email'];
            $avatar    = $input['avatar'];
            $join_date = $input['joinDate'];
            $saves     = $input['saves'] ?? 0;
            $status    = $input['status'];
            $pdo->prepare("INSERT INTO users (name, email, avatar, join_date, saves, status) VALUES (?, ?, ?, ?, ?, ?)")
                ->execute([$name, $email, $avatar, $join_date, $saves, $status]);
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
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
            $id       = $input['id'] ?? null;
            $name     = trim($input['name'] ?? '');
            $email    = trim($input['email'] ?? '');
            $password = trim($input['password'] ?? '');
            $role     = $input['role'] ?? 'editor';

            if (empty($name) || empty($email)) {
                echo json_encode(['status' => 'error', 'message' => 'الاسم والبريد الإلكتروني مطلوبان']);
                exit;
            }

            if ($id) {
                // Update
                if (!empty($password)) {
                    $pdo->prepare("UPDATE dashlogen SET name=?, email=?, password=?, role=? WHERE id=?")
                        ->execute([$name, $email, $password, $role, $id]);
                } else {
                    $pdo->prepare("UPDATE dashlogen SET name=?, email=?, role=? WHERE id=?")
                        ->execute([$name, $email, $role, $id]);
                }
                echo json_encode(['status' => 'success', 'message' => 'تم تعديل الحساب']);
            } else {
                // Insert
                if (empty($password)) {
                    echo json_encode(['status' => 'error', 'message' => 'كلمة المرور مطلوبة عند الإضافة']);
                    exit;
                }
                $hash = password_hash($password, PASSWORD_BCRYPT);
                // Handle the pre-existing UNIQUE constraint on 'username' by generating a unique string.
                $username = uniqid('user_') . rand(100, 999);
                
                try {
                    // Try to insert with username column
                    $pdo->prepare("INSERT INTO dashlogen (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)")
                        ->execute([$name, $username, $email, $password, $role]);
                } catch (PDOException $e) {
                    // Fallback just in case username column doesn't actually exist
                    $pdo->prepare("INSERT INTO dashlogen (name, email, password, role) VALUES (?, ?, ?, ?)")
                        ->execute([$name, $email, $password, $role]);
                }
                echo json_encode(['status' => 'success', 'message' => 'تمت إضافة الحساب']);
            }

        } catch (Exception $e) {
            // Duplicate email
            if ($e->getCode() == 23000) {
                echo json_encode(['status' => 'error', 'message' => 'البريد الإلكتروني مستخدم مسبقاً']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    // ── Delete Dash Login User ───────────────────────────────
    elseif ($action === 'deleteDashUser') {
        try {
            $id = (int)($input['id'] ?? 0);
            if (!$id) {
                echo json_encode(['status' => 'error', 'message' => 'ID غير صالح']);
                exit;
            }
            // Prevent deleting last admin
            $count = $pdo->query("SELECT COUNT(*) FROM dashlogen WHERE role='admin'")->fetchColumn();
            $user  = $pdo->prepare("SELECT role FROM dashlogen WHERE id=?");
            $user->execute([$id]);
            $row   = $user->fetch();
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
                    ':lang'    => $lang,
                    ':badge'   => $data['badge'] ?? '',
                    ':title'   => $data['title'] ?? '',
                    ':v_title' => $data['vision_title'] ?? '',
                    ':v_text'  => $data['vision_text'] ?? '',
                    ':m_title' => $data['mission_title'] ?? '',
                    ':m_text'  => $data['mission_text'] ?? ''
                ]);
            }
            $pdo->commit();
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => "خطأ في القاعدة: " . $e->getMessage()]);
        }
    }
}
?>
