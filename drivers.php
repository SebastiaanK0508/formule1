<?php
require_once 'db_config.php';
/** @var PDO $pdo */

$allDrivers = []; 
try {
    $stmt = $pdo->query("SELECT d.driver_id, d.first_name, d.last_name, d.driver_number, d.flag_url, d.image, t.team_name, t.team_color 
                         FROM drivers d 
                         LEFT JOIN teams t ON d.team_id = t.team_id 
                         WHERE d.is_active = TRUE 
                         ORDER BY d.driver_number ASC");
    $allDrivers = $stmt->fetchAll();
} catch (\PDOException $e) {
    error_log("Fout: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>F1 Starting Grid | F1SITE.NL</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;900&family=Oswald:wght@700;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Inter', 'sans-serif'], 'oswald': ['Oswald', 'sans-serif'] },
                    colors: { 'f1-red': '#E10600' }
                }
            }
        }
    </script>

    <style>
        body { background-color: #0b0b0f; color: #fff; font-style: italic; overflow-x: hidden; }
        .grid-header {
            text-align: center;
            padding: 80px 20px 40px 20px;
        }

        .grid-title {
            font-family: 'Oswald', sans-serif;
            font-size: clamp(3rem, 10vw, 7rem);
            font-weight: 900;
            line-height: 0.8;
            text-transform: uppercase;
            letter-spacing: -2px;
            margin: 0;
        }

        .grid-subtitle {
            font-family: 'Inter', sans-serif;
            font-weight: 900;
            font-size: 0.7rem;
            letter-spacing: 0.5em;
            color: #E10600;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .grid-master-container {
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 5rem 0.5rem 15rem 0.5rem;
        }

        .starting-grid {
            display: grid;
            grid-template-columns: repeat(2, 240px); 
            column-gap: 5rem;
            row-gap: 0;
        }
        @media (max-width: 640px) {
            .starting-grid {
                grid-template-columns: repeat(2, 1fr); 
                max-width: 450px;
                column-gap: 2rem;
            }
        }

        .grid-slot-container:nth-child(odd) {
            padding-bottom: 8rem; 
        }

        .grid-slot-container:nth-child(even) {
            padding-top: 8rem; 
        }

        .grid-box {
            position: relative;
            padding: 25px;
            border-top: 5px solid #fff;
            transition: all 0.4s ease;
        }   
        .grid-box::before {
            content: "";
            position: absolute;
            top: -5px; 
            left: 0;
            width: 5px;
            height: 100px; 
            background: #fff;
        }
        .grid-box::after {
            content: "";
            position: absolute;
            top: -5px;
            right: 0;
            width: 5px;
            height: 100px; 
            background: #fff;
        }
        .grid-box:hover {
            border-top-color: var(--team-color, #fff);
        }

        .grid-box:hover::before,
        .grid-box:hover::after {
            background-color: var(--team-color, #fff);
        }
        .driver-card {
            background: #111116;
            width: 100%;
            aspect-ratio: 1 / 1.45;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255, 255, 255, 0.05);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        .image-box { height: 58%; overflow: hidden; background: #1a1a20; position: relative; }
        .driver-img { width: 100%; height: 100%; object-fit: cover; filter: grayscale(100%); transition: 0.6s cubic-bezier(0.22, 1, 0.36, 1); }
        .driver-card:hover .driver-img { filter: grayscale(0%); transform: scale(1.08); }

        .name-outline {
            -webkit-text-stroke: 1px rgba(255, 255, 255, 0.2);
            color: transparent;
            font-family: 'Oswald';
            font-size: 1.5rem;
            line-height: 1.1;
            text-transform: uppercase;
        }
        .driver-card:hover .name-outline { -webkit-text-stroke: 1.5px #fff; }

        .pos-label {
            position: absolute; top: -40px; left: 0;
            font-family: 'Oswald'; font-weight: 900; font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>

    <?php include 'navigatie/header.php'; ?>
    <header class="grid-header" data-aos="fade-down">
        <h1 class="grid-title">STARTING<span class="text-f1-red">GRID</span></h1>
        <div class="grid-subtitle">Formula 1 Season <?php echo date('Y'); ?></div>
    </header>
    <main class="grid-master-container">
        <section class="starting-grid">
            <?php if (!empty($allDrivers)): ?>
                <?php foreach ($allDrivers as $i => $driver): 
                    $fname = $driver['first_name'] ?? '';
                    $lname = $driver['last_name'] ?? '';
                    $teamColor = $driver['team_color'] ?? '#E10600';
                    $pos = $i + 1;
                ?>
                    <div class="grid-slot-container" style="--team-color: <?php echo $teamColor; ?>;">
                        <div class="grid-box" data-aos="fade-up">
                            <span class="pos-label">P<?php echo $pos; ?></span>
                            
                            <article class="driver-card group">
                                <a href="driver-details.php?slug=<?php echo strtolower($fname.'-'.$lname); ?>" class="absolute inset-0 z-30"></a>

                                <div class="image-box">
                                    <img src="<?php echo htmlspecialchars($driver['image']); ?>" class="driver-img object-top">
                                    <div class="absolute bottom-3 right-3">
                                        <span class="text-2xl font-oswald font-black italic text-white/5 group-hover:text-f1-red transition-all">
                                            #<?php echo $driver['driver_number']; ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="p-4 bg-[#111116] flex-grow flex flex-col justify-center border-t-4" style="border-top-color: <?php echo $teamColor; ?>;">
                                    <span class="text-[8px] font-black text-gray-500 uppercase tracking-widest block mb-1"><?php echo $fname; ?></span>
                                    <h2 class="name-outline italic mb-2 uppercase">
                                        <?php echo $lname; ?>
                                    </h2>
                                    <span class="text-[8px] font-black text-white/30 uppercase tracking-[0.2em]">
                                        <?php echo htmlspecialchars($driver['team_name']); ?>
                                    </span>
                                </div>
                            </article>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script> 
</body>
</html>