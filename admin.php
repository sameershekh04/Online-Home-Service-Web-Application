<?php
require_once 'config.php';

// Add new service
if (isset($_POST['add_service'])) {
    $stmt = $pdo->prepare("INSERT INTO services (category, name, description, price, rating, reviews, image_url, options) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['category'],
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['rating'],
        $_POST['reviews'],
        $_POST['image_url'],
        $_POST['options']
    ]);
    header("Location: admin.php");
    exit();
}

// Update service
if (isset($_POST['update_service'])) {
    $stmt = $pdo->prepare("UPDATE services SET 
                          category = ?, name = ?, description = ?, price = ?, 
                          rating = ?, reviews = ?, image_url = ?, options = ? 
                          WHERE id = ?");
    $stmt->execute([
        $_POST['category'],
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['rating'],
        $_POST['reviews'],
        $_POST['image_url'],
        $_POST['options'],
        $_POST['id']
    ]);
    header("Location: admin.php");
    exit();
}

// Delete service
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin.php");
    exit();
}

// Get all services
$services = $pdo->query("SELECT * FROM services ORDER BY category, name")->fetchAll(PDO::FETCH_ASSOC);
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366F1',
                        secondary: '#EC4899',
                        accent: '#14B8A6',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(to right, #6366F1, #8B5CF6);
        }
        
        .btn-secondary {
            background: linear-gradient(to right, #EC4899, #D946EF);
        }
        
        .table-row:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .table-row:hover {
            background-color: #f1f5f9;
        }
    </style>
</head>
<body>
    <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8 gradient-bg text-white">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold flex items-center">
                        <i class="fas fa-cogs mr-3"></i>
                        Service Management
                    </h1>
                    <div class="text-right">
                        <p class="text-indigo-200">Admin Dashboard</p>
                        <p class="text-sm text-indigo-100">Manage your services efficiently</p>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Add Service Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white p-4">
                            <h2 class="text-xl font-semibold flex items-center">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Add New Service
                            </h2>
                        </div>
                        <div class="p-6">
                            <form method="post" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                        <option value="">Select Category</option>
                                        <option value="switch-socket">Switch & Socket</option>
                                        <option value="fan">Fan</option>
                                        <option value="Light">Light</option>
                                        <option value="Wiring">Wiring</option>
                                        <option value="Doorbell & Security">Doorbell & Security</option>
                                        <option value="MCB/fuse">MCB/Fuse</option>
                                        <option value="Appliances">Appliances</option>
                                        <option value="Book a consultation">Book a Consultation</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Service Name</label>
                                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Price (₹)</label>
                                    <input type="number" name="price" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Rating (0-5)</label>
                                    <input type="number" name="rating" min="0" max="5" step="0.01" value="4.50" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of Reviews</label>
                                    <input type="number" name="reviews" value="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                                    <input type="text" name="image_url" placeholder="path/to/image.png" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Options (if any)</label>
                                    <input type="number" name="options" value="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                                
                                <button type="submit" name="add_service" class="w-full btn-primary text-white py-2 px-4 rounded-lg font-medium hover:opacity-90 transition duration-200">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add Service
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Services Table -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="bg-gradient-to-r from-green-500 to-teal-600 text-white p-4">
                            <h2 class="text-xl font-semibold flex items-center">
                                <i class="fas fa-list mr-2"></i>
                                Existing Services
                            </h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($services as $service): ?>
                                    <tr class="table-row">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $service['id'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= ucfirst(str_replace('-', ' ', $service['category'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $service['name'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹<?= number_format($service['price'], 2) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <?= $service['rating'] ?> ★
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            <a href="?edit=<?= $service['id'] ?>" class="text-indigo-600 hover:text-indigo-900">
                                                <i class="fas fa-edit mr-1"></i> Edit
                                            </a>
                                            |
                                            <a href="?delete=<?= $service['id'] ?>" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash-alt mr-1"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Edit Service Modal -->
            <?php if (isset($_GET['edit'])): 
                $service = $pdo->prepare("SELECT * FROM services WHERE id = ?");
                $service->execute([$_GET['edit']]);
                $service = $service->fetch(PDO::FETCH_ASSOC);
            ?>
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4 overflow-hidden">
                    <div class="bg-gradient-to-r from-orange-500 to-pink-600 text-white p-4">
                        <h2 class="text-xl font-semibold flex items-center">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Service
                        </h2>
                    </div>
                    <div class="p-6">
                        <form method="post" class="space-y-4">
                            <input type="hidden" name="id" value="<?= $service['id'] ?>">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="switch-socket" <?= $service['category'] == 'switch-socket' ? 'selected' : '' ?>>Switch & Socket</option>
                                    <option value="fan" <?= $service['category'] == 'fan' ? 'selected' : '' ?>>Fan</option>
                                    <option value="Light" <?= $service['category'] == 'Light' ? 'selected' : '' ?>>Light</option>
                                    <option value="Wiring" <?= $service['category'] == 'Wiring' ? 'selected' : '' ?>>Wiring</option>
                                    <option value="Doorbell & Security" <?= $service['category'] == 'Doorbell & Security' ? 'selected' : '' ?>>Doorbell & Security</option>
                                    <option value="MCB/fuse" <?= $service['category'] == 'MCB/fuse' ? 'selected' : '' ?>>MCB/Fuse</option>
                                    <option value="Appliances" <?= $service['category'] == 'Appliances' ? 'selected' : '' ?>>Appliances</option>
                                    <option value="Book a consultation" <?= $service['category'] == 'Book a consultation' ? 'selected' : '' ?>>Book a Consultation</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Service Name</label>
                                <input type="text" name="name" value="<?= $service['name'] ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= $service['description'] ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price (₹)</label>
                                <input type="number" name="price" step="0.01" value="<?= $service['price'] ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Rating (0-5)</label>
                                <input type="number" name="rating" min="0" max="5" step="0.01" value="<?= $service['rating'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Number of Reviews</label>
                                <input type="number" name="reviews" value="<?= $service['reviews'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                                <input type="text" name="image_url" value="<?= $service['image_url'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Options (if any)</label>
                                <input type="number" name="options" value="<?= $service['options'] ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            </div>
                            
                            <div class="flex space-x-3 pt-4">
                                <button type="submit" name="update_service" class="flex-1 btn-secondary text-white py-2 px-4 rounded-lg font-medium hover:opacity-90 transition duration-200">
                                    <i class="fas fa-save mr-2"></i>
                                    Update Service
                                </button>
                                <button type="button" onclick="document.querySelector('.modal').remove()" class="flex-1 bg-gray-500 text-white py-2 px-4 rounded-lg font-medium hover:bg-gray-600 transition duration-200">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>