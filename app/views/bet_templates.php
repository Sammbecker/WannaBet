<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Include templates data
require '../includes/templates.php';

// Set bet type from URL (quick or group)
$betType = isset($_GET['type']) && $_GET['type'] === 'group' ? 'group' : 'quick';
$title = $betType === 'group' ? 'Group Bet' : 'Quick Bet';
$returnUrl = $betType === 'group' ? 'group_bet.php' : 'quick_bet.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bet Templates - WannaBet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #000000;
            --secondary-color: #333333;
            --background-color: #f9f9f9;
            --card-bg: #ffffff;
            --text-color: #111111;
            --text-light: #555555;
            --border-color: #eeeeee;
            --accent-color: #000000;
            --hover-accent: #333333;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.05), 0 4px 6px rgba(0,0,0,0.05);
            --gradient-black: linear-gradient(145deg, #000000, #222222);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        /* Navbar */
        .navbar {
            background: var(--gradient-black);
            padding: 1.2rem 0;
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-md);
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: 2px;
            cursor: pointer;
        }

        .user-nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .greeting {
            font-weight: 500;
        }

        .logout-btn {
            color: white;
            text-decoration: none;
            padding: 0.6rem 1.2rem;
            background: rgba(255,255,255,0.15);
            border-radius: 2rem;
            transition: all 0.3s;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-1px);
        }

        /* Container */
        .container {
            max-width: 1000px;
            margin: 3rem auto;
            padding: 0 2rem;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            background: var(--gradient-black);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .go-back {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background: var(--card-bg);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }

        .go-back:hover {
            background: var(--accent-color);
            color: white;
        }

        /* Template Categories */
        .categories {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
            scrollbar-width: thin;
            scrollbar-color: var(--accent-color) var(--background-color);
        }

        .categories::-webkit-scrollbar {
            height: 6px;
        }

        .categories::-webkit-scrollbar-track {
            background: var(--background-color);
        }

        .categories::-webkit-scrollbar-thumb {
            background-color: var(--accent-color);
            border-radius: 6px;
        }

        .category {
            padding: 0.75rem 1.25rem;
            background: var(--card-bg);
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .category.active {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        .category:hover:not(.active) {
            background: #f0f0f0;
        }

        /* Template Grid */
        .templates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .template-card {
            background: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: all 0.3s;
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .template-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            border-color: #ccc;
        }

        .template-header {
            margin-bottom: 1rem;
        }

        .template-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .template-desc {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .template-content {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.95rem;
            flex-grow: 1;
        }

        .template-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
        }

        .suggested-stake {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .use-template {
            padding: 0.5rem 1rem;
            background: var(--accent-color);
            color: white;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.85rem;
        }

        /* Custom Template */
        .custom-template {
            background: var(--gradient-black);
            color: white;
        }

        .custom-template .template-desc {
            color: rgba(255,255,255,0.7);
        }

        .custom-template .template-content {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .custom-template .suggested-stake {
            color: rgba(255,255,255,0.7);
        }

        .custom-template .use-template {
            background: white;
            color: var(--accent-color);
        }

        /* Empty state */
        .empty-templates {
            text-align: center;
            padding: 3rem;
            background: var(--card-bg);
            border-radius: 1rem;
            box-shadow: var(--shadow-sm);
        }

        .empty-icon {
            font-size: 3rem;
            color: var(--text-light);
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .empty-text {
            color: var(--text-light);
            margin-bottom: 1.5rem;
        }

        /* Search */
        .search-container {
            position: relative;
            margin-bottom: 2rem;
        }

        .search-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border-radius: 0.75rem;
            border: 2px solid var(--border-color);
            font-size: 1rem;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1.25rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="logo" onclick="location.href='home.php';">WANNABET</div>
            <div class="user-nav">
                <div class="greeting">Hey <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</div>
                <a href="logout.php" class="logout-btn">LOG OUT</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Choose a Template for <?php echo $title; ?></h1>
            <a href="<?php echo $returnUrl; ?>" class="go-back">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>

        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" id="searchTemplates" placeholder="Search for templates...">
        </div>

        <div class="categories" id="categoriesContainer">
            <div class="category active" data-category="all">
                <i class="fas fa-th-large"></i>
                All Templates
            </div>
            <?php foreach ($betTemplates as $key => $category): ?>
            <div class="category" data-category="<?php echo $key; ?>">
                <i class="fas <?php echo $category['icon']; ?>"></i>
                <?php echo $category['title']; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="templates-grid" id="templatesGrid">
            <?php
            // Loop through all categories and templates
            foreach ($betTemplates as $categoryKey => $category):
                foreach ($category['templates'] as $template):
            ?>
            <div class="template-card <?php echo $categoryKey === 'custom' ? 'custom-template' : ''; ?>" 
                 data-category="<?php echo $categoryKey; ?>" 
                 data-name="<?php echo strtolower($template['name']); ?>" 
                 data-desc="<?php echo strtolower($template['description']); ?>"
                 onclick="selectTemplate('<?php echo addslashes($template['template']); ?>', '<?php echo addslashes($template['stake_suggestion']); ?>', '<?php echo $template['name']; ?>')">
                <div class="template-header">
                    <h3 class="template-title"><?php echo $template['name']; ?></h3>
                    <p class="template-desc"><?php echo $template['description']; ?></p>
                </div>
                <div class="template-content">
                    <?php if (!empty($template['template'])): ?>
                    <p><?php echo $template['template']; ?></p>
                    <?php else: ?>
                    <p>Create your own customized bet from scratch</p>
                    <?php endif; ?>
                </div>
                <div class="template-footer">
                    <?php if (!empty($template['stake_suggestion'])): ?>
                    <div class="suggested-stake">Suggested stake: <?php echo $template['stake_suggestion']; ?></div>
                    <?php else: ?>
                    <div class="suggested-stake">Define your own stake</div>
                    <?php endif; ?>
                    <div class="use-template">Use Template</div>
                </div>
            </div>
            <?php
                endforeach;
            endforeach;
            ?>
        </div>

        <div class="empty-templates" id="emptyState" style="display: none;">
            <div class="empty-icon"><i class="fas fa-search"></i></div>
            <h3 class="empty-title">No templates found</h3>
            <p class="empty-text">Try adjusting your search or select a different category</p>
        </div>
    </div>

    <script>
        // Category selection
        document.querySelectorAll('.category').forEach(category => {
            category.addEventListener('click', function() {
                // Update active state
                document.querySelector('.category.active').classList.remove('active');
                this.classList.add('active');
                
                // Filter templates
                filterTemplates();
            });
        });
        
        // Search functionality
        document.getElementById('searchTemplates').addEventListener('input', filterTemplates);
        
        function filterTemplates() {
            const searchQuery = document.getElementById('searchTemplates').value.toLowerCase();
            const selectedCategory = document.querySelector('.category.active').dataset.category;
            const templates = document.querySelectorAll('.template-card');
            
            let visibleCount = 0;
            
            templates.forEach(template => {
                const category = template.dataset.category;
                const name = template.dataset.name;
                const description = template.dataset.desc;
                
                // Check if template matches category filter
                const categoryMatch = selectedCategory === 'all' || category === selectedCategory;
                
                // Check if template matches search query
                const searchMatch = name.includes(searchQuery) || description.includes(searchQuery);
                
                if (categoryMatch && searchMatch) {
                    template.style.display = 'flex';
                    visibleCount++;
                } else {
                    template.style.display = 'none';
                }
            });
            
            // Show/hide empty state
            document.getElementById('emptyState').style.display = visibleCount === 0 ? 'block' : 'none';
        }
        
        // Template selection
        function selectTemplate(templateText, stakeText, templateName) {
            // Store the selected template in sessionStorage
            sessionStorage.setItem('selectedTemplate', templateText);
            sessionStorage.setItem('selectedStake', stakeText);
            sessionStorage.setItem('templateName', templateName);
            
            // Redirect back to the bet creation page
            window.location.href = '<?php echo $returnUrl; ?>?template=selected';
        }
    </script>
</body>
</html> 