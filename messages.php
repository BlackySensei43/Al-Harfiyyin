<?php 
include 'includes/header.php';
include 'includes/config.php';
include 'includes/auth.php';

// Mark messages as read when viewing a conversation
if (isset($_GET['to'])) {
    $toId = (int)$_GET['to'];
    
    // Mark messages as read
    $query = "UPDATE messages SET is_read = TRUE 
              WHERE receiver_id = ? AND sender_id = ? AND is_read = FALSE";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $_SESSION['user_id'], $toId);
    $stmt->execute();
}

// Send new message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message']) && isset($_POST['receiver_id'])) {
    $message = sanitize($_POST['message']);
    $receiverId = (int)$_POST['receiver_id'];
    $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : null;
    
    $query = "INSERT INTO messages (sender_id, receiver_id, message, post_id) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisi", $_SESSION['user_id'], $receiverId, $message, $postId);
    
    if ($stmt->execute()) {
        // Redirect to prevent form resubmission
        header("Location: messages.php?to=" . $receiverId);
        exit();
    } else {
        $error = "حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.";
    }
}

// Get conversations
$query = "SELECT u.id, u.full_name, u.profile_pic, 
                 MAX(m.created_at) as last_message_time,
                 SUM(CASE WHEN m.is_read = FALSE AND m.receiver_id = ? THEN 1 ELSE 0 END) as unread_count
          FROM users u
          JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
          WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
          GROUP BY u.id
          ORDER BY last_message_time DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$conversations = $stmt->get_result();

// Get messages for a specific conversation
$messages = [];
$otherUser = null;
$postInfo = null;

if (isset($_GET['to'])) {
    $toId = (int)$_GET['to'];
    
    // Get user info
    $query = "SELECT id, full_name, profile_pic FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $toId);
    $stmt->execute();
    $otherUser = $stmt->get_result()->fetch_assoc();
    
    // Get messages
    $query = "SELECT m.*, u.full_name, u.profile_pic 
              FROM messages m
              JOIN users u ON m.sender_id = u.id
              WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
              ORDER BY m.created_at ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiii", $_SESSION['user_id'], $toId, $toId, $_SESSION['user_id']);
    $stmt->execute();
    $messages = $stmt->get_result();
    
    // Get post info if this conversation is about a specific post
    if (isset($_GET['post'])) {
        $postId = (int)$_GET['post'];
        $query = "SELECT id, title FROM posts WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $postInfo = $stmt->get_result()->fetch_assoc();
    }
}
?>

<section class="messages">
    <div class="messages-container">
        <div class="conversations-list">
            <h3>المحادثات</h3>
            
            <?php if($conversations->num_rows > 0): ?>
                <ul>
                    <?php while($conversation = $conversations->fetch_assoc()): ?>
                        <li class="<?php echo isset($_GET['to']) && $_GET['to'] == $conversation['id'] ? 'active' : ''; ?>">
                            <a href="messages.php?to=<?php echo $conversation['id']; ?>">
                                <div class="conversation-user">
                                    <img src="<?php echo $conversation['profile_pic'] ?: 'assets/images/default-profile.png'; ?>" alt="<?php echo $conversation['full_name']; ?>">
                                    <span><?php echo $conversation['full_name']; ?></span>
                                    <?php if($conversation['unread_count'] > 0): ?>
                                        <span class="unread-count"><?php echo $conversation['unread_count']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>لا يوجد محادثات بعد.</p>
            <?php endif; ?>
        </div>
        
        <div class="conversation-view">
            <?php if(isset($otherUser)): ?>
                <div class="conversation-header">
                    <div class="user-info">
                        <img src="<?php echo $otherUser['profile_pic'] ?: 'assets/images/default-profile.png'; ?>" alt="<?php echo $otherUser['full_name']; ?>">
                        <h3><?php echo $otherUser['full_name']; ?></h3>
                    </div>
                    
                    <?php if($postInfo): ?>
                        <div class="post-reference">
                            <p>حول العمل: <?php echo $postInfo['title']; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="messages-list">
                    <?php if($messages->num_rows > 0): ?>
                        <?php while($message = $messages->fetch_assoc()): ?>
                            <div class="message <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'sent' : 'received'; ?>">
                                <div class="message-content">
                                    <p><?php echo $message['message']; ?></p>
                                    <small><?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>لا يوجد رسائل في هذه المحادثة.</p>
                    <?php endif; ?>
                </div>
                
                <div class="message-form">
                    <form action="messages.php" method="POST">
                        <input type="hidden" name="receiver_id" value="<?php echo $otherUser['id']; ?>">
                        <?php if(isset($postInfo)): ?>
                            <input type="hidden" name="post_id" value="<?php echo $postInfo['id']; ?>">
                        <?php endif; ?>
                        <div class="form-group">
                            <textarea name="message" placeholder="اكتب رسالتك هنا..." required></textarea>
                            <button type="submit" class="btn">إرسال</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="no-conversation">
                    <p>اختر محادثة لعرضها أو ابدأ محادثة جديدة.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>