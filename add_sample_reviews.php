<?php
require_once 'config/database.php';

try {
    // First, check if we already have reviews
    $check_reviews = "SELECT COUNT(*) as count FROM Reviews";
    $result = $conn->query($check_reviews);
    $count = $result->fetch_assoc()['count'];
    
    if ($count == 0) {
        // Get a sample user ID
        $user_query = "SELECT user_id FROM Users LIMIT 1";
        $result = $conn->query($user_query);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['user_id'];
            
            // Sample reviews
            $sample_reviews = [
                [
                    'rating' => 5,
                    'text' => "Amazing food and excellent service! The ambiance was perfect for a dinner date. Will definitely come back!",
                    'days_ago' => 2
                ],
                [
                    'rating' => 4,
                    'text' => "Great variety of dishes. The pizza was especially delicious. Service was prompt and friendly.",
                    'days_ago' => 5
                ],
                [
                    'rating' => 5,
                    'text' => "Best restaurant in town! The chef's special was outstanding. Highly recommend the desserts too.",
                    'days_ago' => 1
                ]
            ];
            
            // Insert sample reviews
            $stmt = $conn->prepare("INSERT INTO Reviews (user_id, rating, review_text, created_at) VALUES (?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))");
            
            foreach ($sample_reviews as $review) {
                $stmt->bind_param("iisi", $user_id, $review['rating'], $review['text'], $review['days_ago']);
                $stmt->execute();
            }
            
            echo "Sample reviews added successfully!\n";
        } else {
            echo "No users found in the database. Please create a user first.\n";
        }
    } else {
        echo "Reviews already exist in the database. No sample data added.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
