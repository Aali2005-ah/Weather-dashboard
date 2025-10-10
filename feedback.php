<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
// Handle API requests for feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'submit_feedback') {
        $user_id = $_POST['userId'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $subject = $_POST['subject'];
        $rating = $_POST['rating'];
        $message = $_POST['message'];
        if (empty($subject) || empty($message)) {
            http_response_code(400);
            echo json_encode(['error' => 'Subject and message are required']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback (user_id, name, email,
subject, rating, message) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $email, $subject, $rating, $message]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
    exit; // Stop further execution for API calls
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Weather Dashboard</title>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.4)), url('https://images.unsplash.com/photo-1504608524841-42fe6f032b4b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2060 &q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #001f4d;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            display: flex;
            flex: 1;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(rgba(0, 31, 77, 0.4), rgba(0, 51, 102, 0.5)), url('https://images.unsplash.com/photo-1504608524841-42fe6f032b4b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2060 &q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 30px 20px;
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 100;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 40px;
            font-weight: 700;
            font-size: 1.8rem;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar h2 i {
            color: #083f97;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .nav-links a {
            color: #dbe4ff;
            padding: 14px 16px;
            text-decoration: none;
            border-radius: 10px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            transform: translateX(5px);
        }

        .nav-links a.active {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .nav-links a i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .main {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(3px);
            margin-left: 250px;
        }

        header {
            margin-bottom: 30px;
        }

        h1 {
            font-weight: 800;
            font-size: 2.8rem;
            background: linear-gradient(135deg, #001f4d 0%, #003366 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 10px;
        }

        .feedback-content {
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 5px 25px rgba(0, 31, 77, 0.1);
            margin-bottom: 30px;
        }

        .feedback-section {
            margin-bottom: 30px;
        }

        .feedback-section h2 {
            color: #001f4d;
            font-size: 1.8rem;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e6f0ff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feedback-section h2 i {
            color: #5d9bff;
        }

        .feedback-section p {
            color: #001f4d;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .feedback-form {
            display: grid;
            gap: 20px;
            max-width: 100%;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            color: #001f4d;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 15px;
            border: 2px solid #e6f0ff;
            border-radius: 10px;
            font-size: 1rem;
            background: #f8fbff;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #5d9bff;
            box-shadow: 0 0 0 3px rgba(93, 155, 255, 0.2);
            outline: none;
        }

        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }

        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 5px;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .star-rating input:checked~label,
        .star-rating label:hover,
        .star-rating label:hover~label {
            color: #ffc107;
        }

        .submit-btn {
            background: linear-gradient(rgba(0, 31, 77, 0.7), rgba(0, 51, 102,
                        0.7));
            color: white;
            border: none;
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 31, 77, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 31, 77, 0.3);
        }

        .thank-you {
            background: #e8f4ff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid #001f4d;
            display: none;
        }

        .thank-you h3 {
            color: #001f4d;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .thank-you p {
            color: #56729a;
            margin: 0;
        }

        .feedback-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .feedback-type {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .feedback-type:hover {
            transform: translateY(-5px);
        }

        .feedback-type i {
            font-size: 2.5rem;
            color: #083f97;
            margin-bottom: 15px;
        }

        .feedback-type h3 {
            color: #001f4d;
            margin-bottom: 10px;
        }

        .feedback-type p {
            color: #56729a;
            font-size: 0.9rem;
        }

        @media (max-width: 900px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 20px;
            }

            .nav-links {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
            }

            .nav-links a {
                padding: 10px 15px;
                font-size: 0.9rem;
            }

            .main {
                margin-left: 0;
                padding: 20px;
            }

            h1 {
                font-size: 2.2rem;
            }

            .feedback-content {
                padding: 20px;
            }

            .feedback-section h2 {
                font-size: 1.5rem;
            }

            .feedback-types {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar/Navbar -->
        <aside class="sidebar">
            <h2><i class="fas fa-cloud-sun"></i> Menu</h2>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i>
                    Dashboard</a>
                <a href="favorites.php"><i class="fas fa-star"></i> Favorites</a>
                <a href="history.php"><i class="fas fa-history"></i> History</a>
                <a href="about.php"><i class="fas fa-info-circle"></i> About</a>
                <a href="help.php"><i class="fas fa-question-circle"></i> Help</a>
                <a href="feedback.php" class="active"><i class="fas
fa-comment"></i> Feedback</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>
        <!-- Main Content -->
        <main class="main">
            <header>
                <h1>Feedback</h1>
                <p>We value your input to help us improve the Weather Dashboard</p>
            </header>
            <div class="feedback-content">
                <div class="feedback-section">
                    <h2><i class="fas fa-comment-dots"></i> Share Your
                        Thoughts</h2>
                    <p>Your feedback helps us understand what we're doing well and
                        where we can improve. Please take a
                        moment to share your experience with us.</p>
                    <form class="feedback-form" id="feedbackForm">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" placeholder="Enter your
name"
                                value="<?php echo htmlspecialchars($username); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address (Optional)</label>
                            <input type="email" id="email" placeholder="Enter your
email">
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <select id="subject" required>
                                <option value="">Select a category</option>
                                <option value="bug">Bug Report</option>
                                <option value="suggestion">Feature
                                    Suggestion</option>
                                <option value="improvement">Improvement</option>
                                <option value="general">General Feedback</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Rate Your Experience</label>
                            <div class="star-rating">
                                <input type="radio" id="star5" name="rating"
                                    value="5">
                                <label for="star5" class="star">&#9733;</label>
                                <input type="radio" id="star4" name="rating"
                                    value="4">
                                <label for="star4" class="star">&#9733;</label>
                                <input type="radio" id="star3" name="rating"
                                    value="3">
                                <label for="star3" class="star">&#9733;</label>
                                <input type="radio" id="star2" name="rating"
                                    value="2">
                                <label for="star2" class="star">&#9733;</label>
                                <input type="radio" id="star1" name="rating"
                                    value="1">
                                <label for="star1" class="star">&#9733;</label>
                            </div>
                            <div class="rating-text" id="ratingText">Select a
                                rating</div>
                        </div>
                        <div class="form-group">
                            <label for="message">Your Feedback</label>
                            <textarea id="message" placeholder="Please share your
thoughts, suggestions, or issues..."
                                required></textarea>
                        </div>
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Submit Feedback
                        </button>
                    </form>
                    <div class="thank-you" id="thankYouMessage">
                        <h3><i class="fas fa-check-circle"></i> Thank You!</h3>
                        <p>Your feedback has been submitted successfully. We
                            appreciate you taking the time to help us
                            improve.</p>
                    </div>
                </div>
                <div class="feedback-section">
                    <h2><i class="fas fa-lightbulb"></i> What Kind of Feedback Are
                        We Looking For?</h2>
                    <p>We welcome all types of feedback. Here are some examples of
                        what you might share:</p>
                    <div class="feedback-types">
                        <div class="feedback-type">
                            <i class="fas fa-bug"></i>
                            <h3>Bug Reports</h3>
                            <p>Let us know if you encounter any issues or errors
                                while using the dashboard.</p>
                        </div>
                        <div class="feedback-type">
                            <i class="fas fa-plus-circle"></i>
                            <h3>Feature Requests</h3>
                            <p>Suggest new features or improvements you'd like to
                                see in future updates.</p>
                        </div>
                        <div class="feedback-type">
                            <i class="fas fa-paint-brush"></i>
                            <h3>Design Feedback</h3>
                            <p>Share your thoughts on the user interface, layout,
                                and visual design.</p>
                        </div>
                        <div class="feedback-type">
                            <i class="fas fa-tachometer-alt"></i>
                            <h3>Performance</h3>
                            <p>Tell us about your experience with loading times and
                                overall performance.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        const userId = <?php echo $user_id; ?>;
        // Star rating functionality
        const stars = document.querySelectorAll('.star-rating input');
        const ratingText = document.getElementById('ratingText');
        stars.forEach(star => {
            star.addEventListener('change', function() {
                const rating = this.value;
                const texts = {
                    '1': 'Poor - We\'re sorry to hear that',
                    '2': 'Fair - We\'ll work to improve',
                    '3': 'Good - Thanks for your feedback',
                    '4': 'Very Good - Glad you like it!',
                    '5': 'Excellent - We\'re thrilled!'
                };
                ratingText.textContent = texts[rating];
            });
            star.addEventListener('mouseover', function() {
                const hoverTexts = {
                    '1': 'Poor',
                    '2': 'Fair',
                    '3': 'Good',
                    '4': 'Very Good',
                    '5': 'Excellent'
                };
                ratingText.textContent = hoverTexts[this.value];
            });
        });
        // Reset rating text when mouse leaves the star group
        document.querySelector('.star-rating').addEventListener('mouseleave',
            function() {
                const checkedStar = document.querySelector('.star-rating input:checked');
                if (checkedStar) {
                    const texts = {
                        '1': 'Poor - We\'re sorry to hear that',
                        '2': 'Fair - We\'ll work to improve',
                        '3': 'Good - Thanks for your feedback',
                        '4': 'Very Good - Glad you like it!',
                        '5': 'Excellent - We\'re thrilled!'
                    };
                    ratingText.textContent = texts[checkedStar.value];
                } else {
                    ratingText.textContent = 'Select a rating';
                }
            });
        document.getElementById('feedbackForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new URLSearchParams();
            formData.append('action', 'submit_feedback');
            formData.append('userId', userId);
            formData.append('name', document.getElementById('name').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('subject', document.getElementById('subject').value);
            formData.append('rating',
                document.querySelector('input[name="rating"]:checked')?.value || '');
            formData.append('message', document.getElementById('message').value);
            if (!document.getElementById('subject').value ||
                !document.getElementById('message').value) {
                alert('Please fill in all required fields.');
                return;
            }
            try {
                const response = await fetch('feedback.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (response.ok) {
                    document.getElementById('thankYouMessage').style.display =
                        'block';
                    this.reset();
                    document.getElementById('thankYouMessage').scrollIntoView({
                        behavior: 'smooth'
                    });
                } else {
                    alert('Error submitting feedback: ' + result.error);
                }
            } catch (error) {
                console.error('Error submitting feedback:', error);
                alert('Error submitting feedback. Please try again.');
            }
        });
    </script>
</body>

</html>