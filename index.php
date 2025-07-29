<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "journey";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->query("SELECT * FROM memories ORDER BY created_at DESC");
    $memories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Index connection failed: " . $e->getMessage(), 3, "C:/xampp/htdocs/journey/index_error.log");
    $memories = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Our Journey</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Parisienne&display=swap" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #f5e1ee 0%, #f8c1cc 100%);
      font-family: 'Playfair Display', serif;
      overflow-x: hidden;
    }
    .timeline-container {
      opacity: 0;
      transform: translateY(50px);
      transition: opacity 1s ease, transform 1s ease;
    }
    .timeline-container.visible {
      opacity: 1;
      transform: translateY(0);
    }
    .photo-card {
      background: rgba(255, 255, 255, 0.9);
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }
    .photo-card:hover {
      transform: scale(1.05);
    }
    .heart {
      position: absolute;
      color: #ff6b6b;
      font-size: 1.5rem;
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.2); }
      100% { transform: scale(1); }
    }
    .caption {
      opacity: 0;
      transition: opacity 1s ease 0.5s;
    }
    .photo-card.visible .caption {
      opacity: 1;
    }
    .floating-heart {
      position: fixed;
      color: #ff6b6b;
      font-size: 1.5rem;
      opacity: 0.7;
      animation: float 6s ease-in-out infinite;
      pointer-events: none;
    }
    @keyframes float {
      0% { transform: translateY(100vh); opacity: 0.7; }
      50% { opacity: 0.9; }
      100% { transform: translateY(-100vh); opacity: 0; }
    }
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }
    .modal-content {
      background: rgba(255, 255, 255, 0.95);
      padding: 2rem;
      border-radius: 15px;
      max-width: 500px;
      text-align: center;
      position: relative;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    .modal-content h2 {
      font-family: 'Parisienne', cursive;
      font-size: 2.5rem;
      color: #ff6b6b;
    }
    .close {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 1.5rem;
      cursor: pointer;
    }
    .delete-icon {
      position: absolute;
      bottom: 10px;
      right: 10px;
      color: #ff6b6b;
      font-size: 1.2rem;
      cursor: pointer;
      opacity: 0.7;
      transition: opacity 0.3s ease;
    }
    .delete-icon:hover {
      opacity: 1;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center text-gray-800">
  <div id="introModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>My Dearest</h2>
      <p class="text-gray-600 italic text-lg">
        Every moment with you is a treasure. Let's relive our journey and add new memories together.
      </p>
      <button id="startJourney" class="mt-6 bg-pink-500 text-white py-2 px-4 rounded-full text-lg hover:bg-pink-600 transition duration-300">
        Let's See Our Journey
      </button>
    </div>
  </div>

  <div id="uploadModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Add a Memory</h2>
      <form id="uploadForm" enctype="multipart/form-data" class="space-y-4">
        <input type="file" name="photo" accept="image/*" class="block w-full text-gray-600" required>
        <input type="text" name="title" placeholder="Enter a title for this memory" class="w-full p-2 border rounded-lg" required>
        <textarea name="description" placeholder="Write something special about this moment..." class="w-full p-2 border rounded-lg" rows="4" required></textarea>
        <button type="submit" class="bg-pink-500 text-white py-2 px-4 rounded-full hover:bg-pink-600 transition duration-300">Add Memory</button>
      </form>
      <p id="uploadMessage" class="text-gray-600 mt-2"></p>
    </div>
  </div>

  <div class="text-center mb-12">
    <h1 class="text-5xl md:text-7xl font-bold text-pink-600 mb-4 font-['Parisienne']">Our Journey</h1>
    <p class="text-xl md:text-2xl text-gray-600 italic">A timeline of our unforgettable moments.</p>
    <button id="addMemoryButton" class="mt-6 bg-pink-500 text-white py-3 px-6 rounded-full text-lg hover:bg-pink-600 transition duration-300">
      Add a Memory
    </button>
  </div>

  <div id="timeline" class="timeline-container hidden w-full max-w-4xl mx-auto space-y-12 px-4">
    <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
      <img src="photo1.jpg" alt="First moment" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
      <div class="text-center md:text-left">
        <h2 class="text-2xl font-semibold text-pink-600">The Day We Met</h2>
        <p class="caption text-gray-600 italic">The moment I saw you, my heart knew it was forever.</p>
      </div>
      <span class="heart top-2 right-2">❤️</span>
    </div>
    <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
      <img src="photo2.jpg" alt="First date" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
      <div class="text-center md:text-left">
        <h2 class="text-2xl font-semibold text-pink-600">Our First Date</h2>
        <p class="caption text-gray-600 italic">Your smile lit up my world that day.</p>
      </div>
      <span class="heart top-2 right-2">❤️</span>
    </div>
    <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
      <img src="photo3.jpg" alt="Special moment" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
      <div class="text-center md:text-left">
        <h2 class="text-2xl font-semibold text-pink-600">A Moment to Cherish</h2>
        <p class="caption text-gray-600 italic">With you, every second feels like a dream.</p>
      </div>
      <span class="heart top-2 right-2">❤️</span>
    </div>
    <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
      <img src="photo4.jpg" alt="Shared adventure" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
      <div class="text-center md:text-left">
        <h2 class="text-2xl font-semibold text-pink-600">Our Adventure</h2>
        <p class="caption text-gray-600 italic">Every journey with you is my favorite story.</p>
      </div>
      <span class="heart top-2 right-2">❤️</span>
    </div>
    <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
      <img src="photo5.jpg" alt="Quiet moment" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
      <div class="text-center md:text-left">
        <h2 class="text-2xl font-semibold text-pink-600">A Quiet Moment</h2>
        <p class="caption text-gray-600 italic">In your presence, time stands still.</p>
      </div>
      <span class="heart top-2 right-2">❤️</span>
    </div>
    <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
      <img src="photo6.jpg" alt="Joyful day" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
      <div class="text-center md:text-left">
        <h2 class="text-2xl font-semibold text-pink-600">A Joyful Day</h2>
        <p class="caption text-gray-600 italic">Your laughter is my favorite melody.</p>
      </div>
      <span class="heart top-2 right-2">❤️</span>
    </div>
    <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
      <img src="photo7.jpg" alt="Together forever" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
      <div class="text-center md:text-left">
        <h2 class="text-2xl font-semibold text-pink-600">Together Forever</h2>
        <p class="caption text-gray-600 italic">With you, I’ve found my forever home.</p>
      </div>
      <span class="heart top-2 right-2">❤️</span>
    </div>
    <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
      <img src="photo8.jpg" alt="Starry night" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
      <div class="text-center md:text-left">
        <h2 class="text-2xl font-semibold text-pink-600">Our Starry Night</h2>
        <p class="caption text-gray-600 italic">Under the stars, I promised you my heart.</p>
      </div>
      <span class="heart top-2 right-2">❤️</span>
    </div>
    <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
      <img src="photo9.jpg" alt="New beginning" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
      <div class="text-center md:text-left">
        <h2 class="text-2xl font-semibold text-pink-600">A New Beginning</h2>
        <p class="caption text-gray-600 italic">Every day with you feels like a fresh start.</p>
      </div>
      <span class="heart top-2 right-2">❤️</span>
    </div>
    <?php foreach ($memories as $memory): ?>
      <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
        <img src="<?php echo htmlspecialchars($memory['photo_path']); ?>" alt="Shared memory" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
        <div class="text-center md:text-left flex-1">
          <h2 class="text-2xl font-semibold text-pink-600"><?php echo htmlspecialchars($memory['title']); ?></h2>
          <p class="caption text-gray-600 italic"><?php echo htmlspecialchars($memory['description']); ?></p>
        </div>
        <span class="heart top-2 right-2">❤️</span>
        <span class="delete-icon" data-id="<?php echo $memory['id']; ?>" title="Delete Memory">🗑️</span>
      </div>
    <?php endforeach; ?>
  </div>

  <audio id="backgroundMusic" src="music.mp3" loop></audio>

  <script>
    const introModal = document.getElementById('introModal');
    introModal.style.display = 'flex';

    document.getElementById('startJourney').addEventListener('click', () => {
      introModal.style.display = 'none';
      const timeline = document.getElementById('timeline');
      timeline.classList.remove('hidden');
      setTimeout(() => {
        timeline.classList.add('visible');
        document.querySelectorAll('.photo-card').forEach(card => card.classList.add('visible'));
        try {
          document.getElementById('backgroundMusic').play();
        } catch (error) {
          console.log('Audio playback failed:', error);
        }
      }, 100);
    });

    document.querySelector('#introModal .close').addEventListener('click', () => {
      introModal.style.display = 'none';
    });

    const uploadModal = document.getElementById('uploadModal');
    document.getElementById('addMemoryButton').addEventListener('click', () => {
      uploadModal.style.display = 'flex';
    });

    document.querySelector('#uploadModal .close').addEventListener('click', () => {
      uploadModal.style.display = 'none';
    });

    const uploadForm = document.getElementById('uploadForm');
    uploadForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(uploadForm);
      try {
        const response = await fetch('upload.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();
        document.getElementById('uploadMessage').textContent = result.message;
        if (result.status === 'success') {
          setTimeout(() => {
            window.location.reload();
          }, 1500);
        }
      } catch (error) {
        document.getElementById('uploadMessage').textContent = 'Error uploading memory: ' + error.message;
        console.error('Fetch error:', error);
      }
    });

    document.querySelectorAll('.delete-icon').forEach(icon => {
      icon.addEventListener('click', async () => {
        if (confirm('Are you sure you want to delete this memory?')) {
          const id = icon.getAttribute('data-id');
          try {
            const response = await fetch('delete.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: `id=${id}`
            });
            const result = await response.json();
            if (result.status === 'success') {
              alert('Memory deleted successfully!');
              window.location.reload();
            } else {
              alert('Error deleting memory: ' + result.message);
            }
          } catch (error) {
            alert('Error deleting memory: ' + error.message);
          }
        }
      });
    });

    function createHeart() {
      const heart = document.createElement('span');
      heart.classList.add('floating-heart');
      heart.innerHTML = '❤️';
      heart.style.left = Math.random() * 100 + 'vw';
      heart.style.animationDuration = Math.random() * 3 + 5 + 's';
      document.body.appendChild(heart);
      setTimeout(() => heart.remove(), 6000);
    }
    setInterval(createHeart, 1000);
  </script>
</body>
</html>
