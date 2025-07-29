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
       </style>
     </head>
     <body class="min-h-screen flex flex-col items-center justify-center text-gray-800">
       <!-- Greeting Modal -->
       <div id="greetingModal" class="modal">
         <div class="modal-content">
           <span class="close">&times;</span>
           <h2>My Dearest</h2>
           <p class="text-gray-600 italic text-lg">
             My heart beats only for you. Every moment we've shared is a treasure, and this journey is just the beginning of our forever. Click below to relive our story.
           </p>
           <button id="startJourney" class="mt-6 bg-pink-500 text-white py-2 px-4 rounded-full text-lg hover:bg-pink-600 transition duration-300">
             Let's See Our Journey
           </button>
         </div>
       </div>

       <!-- Upload Modal -->
       <div id="uploadModal" class="modal">
         <div class="modal-content">
           <span class="close">&times;</span>
           <h2>Add a Memory</h2>
           <form id="uploadForm" enctype="multipart/form-data" class="space-y-4">
             <input type="file" name="photo" accept="image/*" class="block w-full text-gray-600" required>
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
         <!-- Static Photos -->
         <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
           <img src="photo1.jpg" alt="Our first moment" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
           <div class="text-center md:text-left">
             <h2 class="text-2xl font-semibold text-pink-600">The Day We Met</h2>
             <p class="caption text-gray-600 italic">The moment I saw you, my heart whispered, "This is the beginning of forever."</p>
           </div>
           <span class="heart top-2 right-2">❤️</span>
         </div>
         <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
           <img src="photo2.jpg" alt="Our first date" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
           <div class="text-center md:text-left">
             <h2 class="text-2xl font-semibold text-pink-600">Our First Date</h2>
             <p class="caption text-gray-600 italic">With every laugh we shared, I fell deeper into the magic of you.</p>
           </div>
           <span class="heart top-2 right-2">❤️</span>
         </div>
         <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
           <img src="photo3.jpg" alt="Our special moment" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
           <div class="text-center md:text-left">
             <h2 class="text-2xl font-semibold text-pink-600">A Moment to Cherish</h2>
             <p class="caption text-gray-600 italic">In your arms, I found the home my soul was searching for.</p>
           </div>
           <span class="heart top-2 right-2">❤️</span>
         </div>
         <!-- Dynamic Photos from Database -->
         <?php foreach ($memories as $memory): ?>
           <div class="photo-card relative p-6 flex flex-col md:flex-row items-center">
             <img src="<?php echo htmlspecialchars($memory['photo_path']); ?>" alt="Shared memory" class="w-full md:w-1/2 rounded-lg mb-4 md:mb-0 md:mr-6">
             <div class="text-center md:text-left">
               <h2 class="text-2xl font-semibold text-pink-600">Our Shared Memory</h2>
               <p class="caption text-gray-600 italic"><?php echo htmlspecialchars($memory['description']); ?></p>
             </div>
             <span class="heart top-2 right-2">❤️</span>
           </div>
         <?php endforeach; ?>
       </div>

       <!-- Background Music -->
       <audio id="backgroundMusic" src="music.mp3" loop></audio>

       <script>
         // Greeting Modal
         const greetingModal = document.getElementById('greetingModal');
         greetingModal.style.display = 'flex';

         document.getElementById('startJourney').addEventListener('click', () => {
           greetingModal.style.display = 'none';
           const timeline = document.getElementById('timeline');
           timeline.classList.remove('hidden');
           setTimeout(() => {
             timeline.classList.add('visible');
             document.querySelectorAll('.photo-card').forEach(card => card.classList.add('visible'));
             document.getElementById('backgroundMusic').play();
           }, 100);
         });

         document.querySelector('#greetingModal .close').addEventListener('click', () => {
           greetingModal.style.display = 'none';
         });

         // Upload Modal
         const uploadModal = document.getElementById('uploadModal');
         const addMemoryButton = document.getElementById('addMemoryButton');
         addMemoryButton.addEventListener('click', () => {
           uploadModal.style.display = 'flex';
         });

         document.querySelector('#uploadModal .close').addEventListener('click', () => {
           uploadModal.style.display = 'none';
         });

         // Handle Form Submission
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
             document.getElementById('uploadMessage').textContent = 'Error uploading memory.';
           }
         });

         // Floating Hearts
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