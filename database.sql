CREATE DATABASE formule1 ;

USE formule1 ;

CREATE TABLE news (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    news_content TEXT NOT NULL,
    image_url VARCHAR(255)
);

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT, 
    username VARCHAR(255) NOT NULL, 
    password_hash VARCHAR(255) NOT NULL 
);

CREATE TABLE drivers (
    driver_id INT PRIMARY KEY AUTO_INCREMENT, 
    first_name VARCHAR(50) NOT NULL,          
    last_name VARCHAR(50) NOT NULL,           
    nationality VARCHAR(50) NOT NULL,         
    date_of_birth DATE,                   
    driver_number INT UNIQUE,                 
    team_name VARCHAR(100),                   
    championships_won INT DEFAULT 0,          
    career_points DECIMAL(10, 2) DEFAULT 0.00,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE            
);

ALTER TABLE drivers
ADD COLUMN image VARCHAR(255); 

ALTER TABLE drivers
ADD COLUMN flag_url VARCHAR(255) NULL;

ALTER TABLE drivers
ADD COLUMN driver_color VARCHAR(50) NULL;

CREATE TABLE teams (
    team_id INT PRIMARY KEY AUTO_INCREMENT,
    team_name VARCHAR(100) NOT NULL UNIQUE,
    team_color VARCHAR(7) NOT NULL,
    full_team_name VARCHAR(255),
    base_location VARCHAR(100),
    team_principal VARCHAR(100),
    technical_director VARCHAR(100),
    championships_won INT DEFAULT 0,
    first_entry_year INT,
    website_url VARCHAR(255),
    logo_url VARCHAR(255),
    current_engine_supplier VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE
);

-- RED BULL RACING
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Red Bull Racing', '#0600EF', 'Oracle Red Bull Racing', 'Milton Keynes, UK', 'Christian Horner', 'Pierre Waché', 7, 2005, 'https://www.redbullracing.com/', 'url_naar_redbull_logo.png', 'Honda RBPT', TRUE);

-- MERCEDES
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Mercedes', '#00D2BE', 'Mercedes-AMG Petronas Formula One Team', 'Brackley, UK', 'Toto Wolff', 'James Allison', 8, 2010, 'https://www.mercedesamgf1.com/', 'url_naar_mercedes_logo.png', 'Mercedes', TRUE);

-- FERRARI
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Ferrari', '#DC0000', 'Scuderia Ferrari', 'Maranello, Italy', 'Frédéric Vasseur', 'Enrico Cardile', 16, 1950, 'https://www.ferrari.com/en-EN/formula1', 'url_naar_ferrari_logo.png', 'Ferrari', TRUE);

-- MCLAREN
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('McLaren', '#FF8700', 'McLaren Formula 1 Team', 'Woking, UK', 'Andrea Stella', 'Peter Prodromou', 8, 1966, 'https://www.mclaren.com/racing/formula-1/', 'url_naar_mclaren_logo.png', 'Mercedes', TRUE);

-- ASTON MARTIN
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Aston Martin', '#006F62', 'Aston Martin Aramco Formula One Team', 'Silverstone, UK', 'Mike Krack', 'Dan Fallows', 0, 2021, 'https://www.astonmartinf1.com/', 'url_naar_aston_martin_logo.png', 'Mercedes', TRUE);

-- ALPINE
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Alpine', '#0090FF', 'BWT Alpine F1 Team', 'Enstone, UK', 'Bruno Famin', 'Matt Harman', 0, 2021, 'https://www.alpinecars.com/en/f1/', 'url_naar_alpine_logo.png', 'Renault', TRUE);

-- WILLIAMS
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Williams', '#64C4FF', 'Williams Racing', 'Grove, UK', 'James Vowles', 'Pat Fry', 9, 1977, 'https://www.williamsf1.com/', 'url_naar_williams_logo.png', 'Mercedes', TRUE);

-- HAAS
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Haas', '#B6BABC', 'MoneyGram Haas F1 Team', 'Kannapolis, USA', 'Ayao Komatsu', 'Simone Resta', 0, 2016, 'https://www.haasf1team.com/', 'url_naar_haas_logo.png', 'Ferrari', TRUE);

-- RB (Racing Bulls)
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('RB', '#6692FF', 'Visa Cash App RB Formula 1 Team', 'Faenza, Italy', 'Laurent Mekies', 'Jody Egginton', 0, 2006, 'https://www.visacashapprb.com/', 'url_naar_rb_logo.png', 'Honda RBPT', TRUE);

-- SAUBER (Kick Sauber)
INSERT INTO teams (team_name, team_color, full_team_name, base_location, team_principal, technical_director, championships_won, first_entry_year, website_url, logo_url, current_engine_supplier, is_active) VALUES
('Sauber', '#52E252', 'Stake F1 Team Kick Sauber', 'Hinwil, Switzerland', 'Alessandro Alunni Bravi', 'James Key', 0, 1993, 'https://www.sauber-group.com/motorsport/formula-1/', 'url_naar_sauber_logo.png', 'Ferrari', TRUE);

ALTER TABLE drivers
ADD COLUMN team_id INT,
ADD CONSTRAINT fk_team
FOREIGN KEY (team_id) REFERENCES teams(team_id);

ALTER TABLE drivers
ADD COLUMN place_of_birth VARCHAR(255);

ALTER TABLE drivers
ADD COLUMN description VARCHAR(255);

--alles hierboven gereed in beide databases--

INSERT INTO drivers (first_name, last_name, nationality, date_of_birth, driver_number, team_name, championships_won, career_points, image, is_active) VALUES
('Max', 'Verstappen', 'Dutch', '1997-09-30', 1, 'Red Bull Racing', 3, 2850.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Max+Verstappen', TRUE),
('Lewis', 'Hamilton', 'British', '1985-01-07', 44, 'Ferrari', 7, 4850.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Lewis+Hamilton', TRUE), -- Overstap naar Ferrari
('Charles', 'Leclerc', 'Monegasque', '1997-10-16', 16, 'Ferrari', 0, 1400.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Charles+Leclerc', TRUE),
('Lando', 'Norris', 'British', '1999-11-13', 4, 'McLaren', 0, 950.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Lando+Norris', TRUE),
('Fernando', 'Alonso', 'Spanish', '1981-07-29', 14, 'Aston Martin', 2, 2350.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Fernando+Alonso', TRUE),
('George', 'Russell', 'British', '1998-02-15', 63, 'Mercedes', 0, 650.00, 'https://placehold.co/150x150/000000/FFFFFF?text=George+Russell', TRUE),
('Yuki', 'Tsunoda', 'Japanese', '2000-05-11', 22, 'Red Bull Racing', 0, 200.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Yuki+Tsunoda', TRUE), -- Overstap naar Red Bull
('Carlos', 'Sainz', 'Spanish', '1994-09-01', 55, 'Williams', 0, 1100.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Carlos+Sainz', TRUE), -- Overstap naar Williams
('Oscar', 'Piastri', 'Australian', '2001-04-06', 81, 'McLaren', 0, 300.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Oscar+Piastri', TRUE),
('Nico', 'Hulkenberg', 'German', '1987-08-19', 27, 'Sauber', 0, 580.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Nico+Hulkenberg', TRUE), -- Overstap naar Sauber
('Lance', 'Stroll', 'Canadian', '1998-10-29', 18, 'Aston Martin', 0, 320.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Lance+Stroll', TRUE),
('Alexander', 'Albon', 'Thai', '1996-03-23', 23, 'Williams', 0, 220.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Alexander+Albon', TRUE),
('Esteban', 'Ocon', 'French', '1996-09-17', 31, 'Haas', 0, 420.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Esteban+Ocon', TRUE), -- Overstap naar Haas
('Pierre', 'Gasly', 'French', '1996-02-07', 10, 'Alpine', 0, 480.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Pierre+Gasly', TRUE),
('Liam', 'Lawson', 'New Zealander', '2002-02-11', 30, 'RB', 0, 10.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Liam+Lawson', TRUE); -- Overstap naar RB

-- Nieuwe coureurs voor 2025
INSERT INTO drivers (first_name, last_name, nationality, date_of_birth, driver_number, team_name, championships_won, career_points, image, is_active) VALUES
('Andrea Kimi', 'Antonelli', 'Italian', '2006-08-25', 12, 'Mercedes', 0, 0.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Kimi+Antonelli', TRUE),
('Oliver', 'Bearman', 'British', '2005-05-08', 87, 'Haas', 0, 0.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Oliver+Bearman', TRUE),
('Jack', 'Doohan', 'Australian', '2003-01-20', 7, 'Alpine', 0, 0.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Jack+Doohan', TRUE),
('Gabriel', 'Bortoleto', 'Brazilian', '2004-10-14', 5, 'Sauber', 0, 0.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Gabriel+Bortoleto', TRUE),
('Isack', 'Hadjar', 'French', '2004-09-28', 6, 'RB', 0, 0.00, 'https://placehold.co/150x150/000000/FFFFFF?text=Isack+Hadjar', TRUE);

CREATE TABLE circuits (
    circuit_key VARCHAR(50) PRIMARY KEY,       
    title VARCHAR(100) NOT NULL,               
    grandprix VARCHAR(100) NOT NULL,           
    location VARCHAR(100) NOT NULL,            
    map_url VARCHAR(255),                      
    first_gp_year INT,                         
    lap_count INT,                             
    circuit_length_km DECIMAL(10, 3),          
    race_distance_km DECIMAL(10, 3),           
    lap_record VARCHAR(100),                   
    description TEXT,                          
    highlights TEXT                            
);

INSERT INTO circuits (
    circuit_key, title, grandprix, location, map_url, first_gp_year,
    lap_count, circuit_length_km, race_distance_km, lap_record, description, highlights
) VALUES
('australia', 'Albert Park Circuit', 'Australian Grand Prix', 'Melbourne, Australië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Australia_Circuit.png', 1996, 58, 5.278, 306.124, '1:19.813 (Charles Leclerc, 2024)', 'Een semi-stratencircuit rondom het Albert Park Lake, bekend om zijn snelle, vloeiende lay-out en uitdagende bochten. Het is vaak de seizoensopener.', 'Semi-stratencircuit, Snelle en vloeiende lay-out, Vaak seizoensopener.'),
('china', 'Shanghai International Circuit', 'Chinese Grand Prix', 'Shanghai, China', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/China_Circuit.png', 2004, 56, 5.451, 305.066, '1:32.238 (Michael Schumacher, 2004)', 'Ontworpen met de vorm van het Chinese karakter "Shang" (boven/omhoog), staat bekend om zijn unieke bochtencombinaties en lange rechte stukken.', 'Unieke "Shang" vorm, Lange rechte stukken, Uitdagende bochtencombinaties.'),
('japan', 'Suzuka International Racing Course', 'Japanese Grand Prix', 'Suzuka, Japan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Japan_Circuit.png', 1987, 53, 5.807, 307.471, '1:30.983 (Lewis Hamilton, 2019)', 'Een favoriet onder coureurs vanwege zijn unieke "achtbaan"-lay-out met een crossover. Bekend om zijn snelle, technische secties en de iconische 130R bocht.', 'Unieke crossover lay-out, Technische en snelle bochten, Iconische 130R.'),
('bahrain', 'Bahrain International Circuit', 'Bahrain Grand Prix', 'Sakhir, Bahrein', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Bahrain_Circuit.png', 2004, 57, 5.412, 308.238, '1:31.447 (Pedro de la Rosa, 2005)', 'Een modern circuit in de woestijn, vaak de openingsrace. Bekend om zijn races onder kunstlicht en de uitdaging van zand op de baan.', 'Nachtrace onder kunstlicht, Woestijnomgeving, Zand op de baan.'),
('saudi_arabia', 'Jeddah Corniche Circuit', 'Saudi Arabian Grand Prix', 'Jeddah, Saoedi-Arabië', 'https://www.formula1.com/content/dam/fom-website/2021/Saudi%20Arabia/jeddah-circuit-map.png', 2021, 50, 6.174, 308.450, '1:30.734 (Lewis Hamilton, 2021)', 'Het snelste stratencircuit op de kalender, met veel blinde bochten en hoge snelheden langs de kustlijn.', 'Snelste stratencircuit, Veel blinde bochten, Hoge snelheden.'),
('miami', 'Miami International Autodrome', 'Miami Grand Prix', 'Miami Gardens, VS', 'https://www.formula1.com/content/dam/fom-website/2022/Miami/Miami_Circuit.png', 2022, 57, 5.412, 308.370, '1:29.708 (Max Verstappen, 2023)', 'Een speciaal gebouwd circuit rondom het Hard Rock Stadium, met een mix van snelle secties en krappe bochten, en een iconische "nep-haven".', 'Speciaal gebouwd circuit, Mix van snelle en krappe bochten, "Nep-haven" feature.'),
('emilia_romagna', 'Autodromo Internazionale Enzo e Dino Ferrari', 'Emilia Romagna Grand Prix', 'Imola, Italië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Imola_Circuit.png', 1980, 63, 4.909, 309.267, '1:15.484 (Lewis Hamilton, 2020)', 'Een historisch circuit met een ouderwetse lay-out, smal en uitdagend, bekend om zijn snelle chicanes en de Tamburello-bocht.', 'Historisch circuit, Smal en uitdagend, Snelle chicanes.'),
('monaco', 'Circuit de Monaco', 'Monaco Grand Prix', 'Monaco', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Monaco_Circuit.png', 1950, 78, 3.337, 260.286, '1:12.909 (Lewis Hamilton, 2021)', 'Het meest glamoureuze en veeleisende stratencircuit, waar precisie en moed essentieel zijn. Overtaken is hier extreem moeilijk.', 'Glamoureus stratencircuit, Zeer veeleisend, Overtaken is moeilijk.'),
('spain', 'Circuit de Barcelona-Catalunya', 'Spanish Grand Prix', 'Barcelona, Spanje', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Spain_Circuit.png', 1991, 66, 4.675, 308.424, '1:18.149 (Max Verstappen, 2021)', 'Dit circuit is een vaste waarde op de kalender en staat bekend om zijn uitgebreide testmogelijkheden in het voorseizoen. Het biedt een mix van snelle en langzame bochten, wat het een goede indicator maakt voor de algemene prestaties van een auto. De lange laatste bocht is cruciaal voor de banden.', 'Vaak gebruikt voor wintertests, Mix van snelle en langzame secties, De laatste sector is cruciaal voor bandenmanagement.'),
('canada', 'Circuit Gilles Villeneuve', 'Canadian Grand Prix', 'Montreal, Canada', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Canada_Circuit.png', 1978, 70, 4.361, 305.270, '1:13.078 (Valtteri Bottas, 2019)', 'Gelegen op een eiland in Montreal, bekend om zijn lange rechte stukken, krappe chicanes en de beruchte "Wall of Champions".', 'Gelegen op een eiland, Lange rechte stukken, "Wall of Champions".'),
('austria', 'Red Bull Ring', 'Austrian Grand Prix', 'Spielberg, Oostenrijk', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Austria_Circuit.png', 1970, 71, 4.318, 306.452, '1:05.619 (Carlos Sainz, 2020)', 'Een kort, snel en heuvelachtig circuit met veel hoogteverschillen, wat zorgt voor spectaculaire inhaalacties.', 'Kort en snel circuit, Veel hoogteverschillen, Spectaculaire inhaalacties.'),
('great_britain', 'Silverstone Circuit', 'British Grand Prix', 'Silverstone, Groot-Brittannië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Great_Britain_Circuit.png', 1950, 52, 5.891, 306.198, '1:27.097 (Max Verstappen, 2020)', 'De thuisbasis van de Britse motorsport, bekend om zijn snelle, vloeiende secties zoals Maggotts, Becketts en Chapel.', 'Historisch circuit, Snelle en vloeiende secties, Iconische bochtencombinaties.'),
('belgium', 'Circuit de Spa-Francorchamps', 'Belgian Grand Prix', 'Stavelot, België', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Belgium_Circuit.png', 1950, 44, 7.004, 308.052, '1:46.286 (Valtteri Bottas, 2018)', 'Een van de meest legendarische en langste circuits, bekend om zijn snelle, vloeiende lay-out, hoogteverschillen en de iconische Eau Rouge-Raidillon combinatie.', 'Langste circuit op de kalender, Veel hoogteverschillen, Iconische Eau Rouge.'),
('hungary', 'Hungaroring', 'Hungarian Grand Prix', 'Mogyoród, Hongarije', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Hungary_Circuit.png', 1986, 70, 4.381, 306.670, '1:16.627 (Lewis Hamilton, 2020)', 'Een bochtig circuit dat vaak wordt vergeleken met een kartbaan, waar inhalen lastig is en de nadruk ligt op aerodynamische grip.', 'Bochtig en technisch, Moeilijk om in te halen, Hoge aerodynamische grip vereist.'),
('netherlands', 'Circuit Zandvoort', 'Dutch Grand Prix', 'Zandvoort, Nederland', 'https://www.formula1.com/content/dam/fom-website/2020/Netherlands/Zandvoort_Circuit.png', 1952, 72, 4.259, 306.648, '1:11.097 (Lewis Hamilton, 2021)', 'Een klassiek circuit met duinen en banking bochten, bekend om zijn uitdagende lay-out en de enthousiaste Nederlandse fans.', 'Circuit in de duinen, Banking bochten, Enthousiaste fans.'),
('italy', 'Autodromo Nazionale Monza', 'Italian Grand Prix', 'Monza, Italië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Italy_Circuit.png', 1950, 53, 5.793, 306.720, '1:21.046 (Rubens Barrichello, 2004)', 'De "Temple of Speed", bekend om zijn lange rechte stukken en snelle chicanes, waar topsnelheid cruciaal is.', 'De "Temple of Speed", Lange rechte stukken, Snelle chicanes.'),
('azerbaijan', 'Baku City Circuit', 'Azerbaijan Grand Prix', 'Baku, Azerbeidzjan', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Azerbaijan_Circuit.png', 2016, 51, 6.003, 306.049, '1:43.009 (Charles Leclerc, 2019)', 'Een stratencircuit met een mix van extreem lange rechte stukken en een zeer smal, technisch gedeelte rondom het kasteel.', 'Stratencircuit, Extreem lange rechte stukken, Smal kasteelgedeelte.'),
('singapore', 'Marina Bay Street Circuit', 'Singapore Grand Prix', 'Singapore', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Singapore_Circuit.png', 2008, 62, 5.063, 313.870, '1:35.867 (Lewis Hamilton, 2018)', 'Het eerste nachtrace-circuit in de F1-geschiedenis, bekend om zijn hoge luchtvochtigheid, hobbelige oppervlak en fysiek veeleisende lay-out.', 'Eerste nachtrace, Hoge luchtvochtigheid, Fysiek veeleisend.'),
('usa', 'Circuit of the Americas', 'United States Grand Prix', 'Austin, VS', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/USA_Circuit.png', 2012, 56, 5.513, 308.405, '1:36.169 (Charles Leclerc, 2019)', 'Een modern circuit met een unieke mix van secties geïnspireerd op andere beroemde circuits, inclusief een steile klim naar bocht 1.', 'Moderne lay-out, Geïnspireerd op andere circuits, Steile klim naar bocht 1.'),
('mexico', 'Autódromo Hermanos Rodríguez', 'Mexico City Grand Prix', 'Mexico-Stad, Mexico', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Mexico_Circuit.png', 1963, 71, 4.304, 305.354, '1:17.774 (Valtteri Bottas, 2021)', 'Gelegen op grote hoogte, wat de motoren en aerodynamica uitdaagt. Bekend om de sfeervolle Foro Sol-stadionsectie.', 'Hoge hoogte, Uitdaging voor motoren/aerodynamica, Foro Sol stadion.'),
('brazil', 'Autódromo José Carlos Pace', 'São Paulo Grand Prix', 'São Paulo, Brazilië', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Brazil_Circuit.png', 1973, 71, 4.309, 305.909, '1:10.540 (Valtteri Bottas, 2018)', 'Een historisch circuit met een korte, intense lay-out en veel hoogteverschillen, vaak het toneel van dramatische races.', 'Historisch circuit, Korte en intense lay-out, Dramatische races.'),
('las_vegas', 'Las Vegas Strip Circuit', 'Las Vegas Grand Prix', 'Paradise, VS', 'https://www.formula1.com/content/dam/fom-website/2023/Las%20Vegas/Las_Vegas_Circuit.png', 2023, 50, 6.201, 309.958, '1:34.876 (Lando Norris, 2024)', 'Een nieuw stratencircuit dat over de beroemde Las Vegas Strip loopt, bekend om zijn hoge snelheden en de spectaculaire nachtelijke setting.', 'Nieuw stratencircuit, Over de Las Vegas Strip, Spectaculaire nachtrace.'),
('qatar', 'Lusail International Circuit', 'Qatar Grand Prix', 'Lusail, Qatar', 'https://www.formula1.com/content/dam/fom-website/2023/Qatar/Qatar_Circuit.png', 2021, 57, 5.419, 308.611, '1:22.384 (Lando Norris, 2024)', 'Een modern circuit in de woestijn, bekend om zijn snelle, vloeiende lay-out en de race onder kunstlicht.', 'Modern woestijncircuit, Snelle en vloeiende lay-out, Nachtrace.'),
('abu_dhabi', 'Yas Marina Circuit', 'Abu Dhabi Grand Prix', 'Abu Dhabi, VAE', 'https://www.formula1.com/content/dam/fom-website/2018-redesign-assets/Circuit%20maps%2016x9/Abu_Dhabi_Circuit.png', 2009, 58, 5.281, 306.183, '1:26.103 (Max Verstappen, 2021)', 'Een modern circuit met indrukwekkende faciliteiten, bekend om zijn jachthaven en de race die begint bij zonsondergang en eindigt in het donker.', 'Moderne faciliteiten, Jachthaven setting, Zonsondergang naar nachtrace.');

ALTER TABLE circuits ADD COLUMN calendar_order INT UNIQUE;

UPDATE circuits SET calendar_order = 1 WHERE circuit_key = 'australia';
UPDATE circuits SET calendar_order = 2 WHERE circuit_key = 'china';
UPDATE circuits SET calendar_order = 3 WHERE circuit_key = 'japan';
UPDATE circuits SET calendar_order = 4 WHERE circuit_key = 'bahrain';
UPDATE circuits SET calendar_order = 5 WHERE circuit_key = 'saudi_arabia';
UPDATE circuits SET calendar_order = 6 WHERE circuit_key = 'miami';
UPDATE circuits SET calendar_order = 7 WHERE circuit_key = 'emilia_romagna';
UPDATE circuits SET calendar_order = 8 WHERE circuit_key = 'monaco';
UPDATE circuits SET calendar_order = 9 WHERE circuit_key = 'spain';
UPDATE circuits SET calendar_order = 10 WHERE circuit_key = 'canada';
UPDATE circuits SET calendar_order = 11 WHERE circuit_key = 'austria';
UPDATE circuits SET calendar_order = 12 WHERE circuit_key = 'great_britain';
UPDATE circuits SET calendar_order = 13 WHERE circuit_key = 'belgium';
UPDATE circuits SET calendar_order = 14 WHERE circuit_key = 'hungary';
UPDATE circuits SET calendar_order = 15 WHERE circuit_key = 'netherlands';
UPDATE circuits SET calendar_order = 16 WHERE circuit_key = 'italy';
UPDATE circuits SET calendar_order = 17 WHERE circuit_key = 'azerbaijan';
UPDATE circuits SET calendar_order = 18 WHERE circuit_key = 'singapore';
UPDATE circuits SET calendar_order = 19 WHERE circuit_key = 'usa';
UPDATE circuits SET calendar_order = 20 WHERE circuit_key = 'mexico';
UPDATE circuits SET calendar_order = 21 WHERE circuit_key = 'brazil';
UPDATE circuits SET calendar_order = 22 WHERE circuit_key = 'las_vegas';
UPDATE circuits SET calendar_order = 23 WHERE circuit_key = 'qatar';
UPDATE circuits SET calendar_order = 24 WHERE circuit_key = 'abu_dhabi';

CREATE TABLE race_results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    circuit_key VARCHAR(50) NOT NULL, -- Verwijst naar circuit_key in 'circuits' tabel
    driver_id INT NOT NULL,           -- Verwijst naar driver_id in 'drivers' tabel
    race_year INT NOT NULL,           -- Jaar van de race
    race_type ENUM('Race', 'Sprint') NOT NULL, -- Type race (bijv. Hoofd race, Sprint)
    position INT NOT NULL,            -- Eindpositie van de coureur
    points DECIMAL(5,2) NOT NULL DEFAULT 0.00, -- Aantal behaalde punten
    laps_completed INT NOT NULL,      -- Aantal voltooide ronden
    finish_status VARCHAR(50),        -- Bijv. 'Finished', 'DNF', 'Disqualified'
    fastest_lap_time VARCHAR(20),     -- Snelste rondetijd (optioneel, bijv. '1:23.456')
    time_offset VARCHAR(50),          -- Tijdverschil met winnaar (bijv. '+1.234s' of '1 lap')
    pole_position BOOLEAN DEFAULT FALSE, -- Of deze coureur pole position had
    FOREIGN KEY (circuit_key) REFERENCES circuits(circuit_key),
    FOREIGN KEY (driver_id) REFERENCES drivers(driver_id),
    UNIQUE (circuit_key, race_year, race_type, driver_id) -- Voorkom dubbele resultaten voor dezelfde coureur in dezelfde race
);

ALTER TABLE circuits
ADD COLUMN race_year INT;

CREATE TABLE points_system (
    position INT PRIMARY KEY,
    points DECIMAL(5,2) NOT NULL
);

-- Invoegen van standaard F1 puntensysteem (top 10)
INSERT INTO points_system (position, points) VALUES
(1, 25.00),
(2, 18.00),
(3, 15.00),
(4, 12.00),
(5, 10.00),
(6, 8.00),
(7, 6.00),
(8, 4.00),
(9, 2.00),
(10, 1.00);

-- Invoegen van 0 punten voor posities 11 t/m 20 (indien van toepassing)
INSERT INTO points_system (position, points) VALUES
(11, 0.00),
(12, 0.00),
(13, 0.00),
(14, 0.00),
(15, 0.00),
(16, 0.00),
(17, 0.00),
(18, 0.00),
(19, 0.00),
(20, 0.00);
