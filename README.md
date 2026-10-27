# GymWarriors
Trello:
https://trello.com/invite/b/6a9911e19ee62f7a2983ce29/ATTIc923f27b940afdb263b096b354b5124c4122CE5D/my

Google Docs:
https://docs.google.com/document/d/1oEPkM7nhTVBrXZoFuMKZvvzTzmru_mypJKF636SYJiE/edit?usp=sharing

Canva:
https://canva.link/1q0ip1cmtbxqaz7

# GymWarriors — projekta dokumentācija

## 1. Par projektu

**GymWarriors** ir tīmekļa lietotne sporta zāles dalības, treniņu un lietotāju pārvaldībai.

Projekts izstrādāts, izmantojot Laravel un Vue.js. Lietotne apvieno lietotāja profilu, sporta zāles dalības plānus, kaloriju un treniņu funkcionalitāti, progresa pārskatus un administratora paneli.

Projekta mērķis ir izveidot vienotu platformu, kurā lietotāji var pārvaldīt savu sporta zāles dalību un sekot līdzi treniņiem, savukārt administratori var pārvaldīt sistēmas datus.

## 2. Izmantotās tehnoloģijas

| Tehnoloģija   | Pielietojums                                                 |
| ------------- | ------------------------------------------------------------ |
| PHP           | Servera puses programmēšanas valoda                          |
| Laravel       | Backend, maršrutēšana, autentifikācija un datubāzes darbības |
| Vue.js        | Lietotāja saskarne                                           |
| Vue Router    | Pārvietošanās starp lietotnes lapām                          |
| Vite          | Frontend izstrādes serveris un resursu apstrāde              |
| SQLite        | Relāciju datubāze                                            |
| Git un GitHub | Versiju kontrole un projekta koplietošana                    |

## 3. Projekta struktūra

Svarīgākās projekta mapes un faili:

```text
gymwarriors/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   └── Models/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── database.sqlite
├── resources/
│   └── js/
│       ├── pages/
│       ├── router/
│       └── app.js
├── routes/
│   └── web.php
├── tests/
│   └── Feature/
├── public/
├── package.json
├── vite.config.js
└── README.md
```

### Galveno mapju nozīme

* `app/Http/Controllers/` — kontrolieri, kas apstrādā pieprasījumus un lietotnes loģiku.
* `app/Http/Middleware/` — starpprogrammatūra piekļuves un citu pieprasījumu noteikumu pārbaudei.
* `app/Models/` — modeļi, kas pārstāv datubāzes objektus.
* `database/migrations/` — datubāzes tabulu un to struktūras izmaiņu apraksti.
* `database/seeders/` — sākotnējo datu ievietošana datubāzē.
* `resources/js/pages/` — Vue lapu komponenti.
* `resources/js/router/` — frontend maršrutu konfigurācija.
* `routes/web.php` — Laravel maršruti, tostarp lietotnes API galapunkti.
* `tests/Feature/` — funkcionalitātes un integrācijas testiem paredzētie faili.

## 4. Lietotnes lapas

Frontend maršrutētājā ir reģistrētas šādas lapas:

| Maršruts    | Lapa         | Pielietojums                             |
| ----------- | ------------ | ---------------------------------------- |
| `/`         | HomePage     | Sākumlapa                                |
| `/login`    | LoginPage    | Pieteikšanās                             |
| `/register` | RegisterPage | Reģistrācija                             |
| `/profile`  | ProfilePage  | Lietotāja profils un dalības informācija |
| `/plans`    | PlansPage    | Dalības plāni un to iegāde               |
| `/calories` | CaloriesPage | Kaloriju funkcionalitāte                 |
| `/workout`  | WorkoutPage  | Treniņu funkcionalitāte                  |
| `/admin`    | AdminPage    | Administratora panelis                   |
| `/reports`  | ReportsPage  | Pārskati un statistika                   |

Frontend maršruti nosaka, kura Vue komponente tiek parādīta attiecīgajā adresē. Faktiskā piekļuve aizsargātajiem datiem tiek kontrolēta arī backend pusē.

## 5. Galvenā funkcionalitāte

### Lietotāja konts

Sistēmā ir reģistrēti lietotāji ar tādiem datiem kā vārds, uzvārds, e-pasts un tālrunis.

Backend pusē ir pieejams autentificēts `/api/user` galapunkts lietotāja informācijas iegūšanai.

### Sporta zāles dalības plāni

Projektā ir divi sākotnēji definēti dalības plāni:

* **Parastais** — 19,99 €, ar iespēju izvēlēties līdz divām sporta zāles lokācijām.
* **Pro** — 29,99 €, ar piekļuvi visām aktīvajām lokācijām.

Dalības iegādes procesā backend pārbauda izvēlēto plānu un lokācijas. Dalība tiek reģistrēta datubāzē, un tai tiek piešķirti 30 tokeni. Dalības ilgums ir 30 dienas.

API galapunkti:

* `GET /api/plans` — pieejamo plānu iegūšana.
* `GET /api/locations` — aktīvo lokāciju iegūšana.
* `POST /api/plans/purchase` — dalības iegāde.
* `GET /api/profile` — lietotāja profila un aktīvās dalības informācija.

### Treniņi

Projektā ir vingrinājumu un treniņu veidņu sākotnējie dati. Piemēri:

* Barbell Squat
* Bench Press
* Lat Pulldown
* Romanian Deadlift
* Push-ups
* Plank
* Treadmill Run
* Cycling

Ir definētas arī pilna ķermeņa spēka treniņa un kardio treniņa veidnes.

### Kalorijas un progress

Projektā ir kaloriju profila, ķermeņa rādītāju un progresa API galapunkti. Tie paredzēti ar kalorijām saistīto datu, ķermeņa rādītāju un lietotāja progresa apstrādei.

Precīza aprēķinu loģika un visu šo funkciju lietotāja darbību secība jāskata attiecīgajos Vue komponentos un Laravel kontrolieros.

### Pārskati

Pārskatu lapa atrodas `resources/js/pages/ReportsPage.vue`. Tā pieprasa datus no `/api/reports`.

Administratora pārskatiem ir paredzēts arī `/api/admin/reports` galapunkts.

### Administratora panelis

Administratora saskarne atrodas `resources/js/pages/AdminPage.vue`.

Projektā ir arī atsevišķa lietotāju pārvaldības funkcionalitāte un `EnsureUserIsNotBanned` middleware. Šo komponentu precīzās atļaujas un darbības jāizvērtē kopā ar backend maršrutiem un testiem.

## 6. Datubāze

Projekts izmanto SQLite datubāzi:

```text
database/database.sqlite
```

Datubāzes struktūru apraksta Laravel migrācijas, bet sākotnējos datus ievieto seederi.

Svarīgākie zināmie datubāzes objekti:

| Tabula               | Nozīme                           |
| -------------------- | -------------------------------- |
| `users`              | Lietotāju konti                  |
| `plans`              | Dalības plāni                    |
| `locations`          | Sporta zāles lokācijas           |
| `plan_purchases`     | Lietotāju iegādātās dalības      |
| `purchase_locations` | Saikne starp dalību un lokācijām |

Dalības tabulā `plan_purchases` tiek glabāts lietotājs, plāns, sākuma un beigu datums, piešķirto un atlikušo tokenu skaits, pirkuma datums un statuss.

Papildu tabulas un to attiecības jāizpēta `database/migrations/` mapē.

### Sākotnējo datu ievietošana

Projektā ir pieejami seederi:

* `PlanSeeder.php`
* `LocationSeeder.php`
* `WorkoutSeeder.php`

Palaist visus `DatabaseSeeder` definētos seederus:

```bash
php artisan db:seed
```

Atsevišķu seederi var palaist šādi:

```bash
php artisan db:seed --class=PlanSeeder
php artisan db:seed --class=LocationSeeder
php artisan db:seed --class=WorkoutSeeder
```

**Uzmanību:** pašreizējie `PlanSeeder` un `LocationSeeder` izmanto `create()`, tāpēc atkārtota palaišana var izveidot dublikātus. Pirms seederu palaišanas esošā datubāzē pārbaudi to darbību.

## 7. Projekta palaišana lokāli vai Codespaces

### Prasības

* PHP un projektam nepieciešamās PHP paplašinājumu versijas.
* Composer.
* Node.js un npm.
* SQLite atbalsts PHP vidē.

### 1. Iegūsti projektu

Ja repozitorijs vēl nav pieejams:

```bash
git clone <REPOSITORY_URL>
cd gymwarriors
```

Ja projekts jau ir atvērts GitHub Codespaces, pārej uz tā saknes mapi.

### 2. Instalē backend atkarības

```bash
composer install
```

### 3. Sagatavo vides konfigurāciju

Ja `.env` fails vēl nav izveidots:

```bash
cp .env.example .env
php artisan key:generate
```

Pārliecinies, ka `.env` konfigurē SQLite un izmanto pareizo datubāzes failu.

Ja nepieciešams izveidot jaunu datubāzi, vispirms noskaidro, vai projektā jau ir sagatavots `database/database.sqlite` fails un vai tajā atrodas vajadzīgie dati.

### 4. Instalē frontend atkarības

```bash
npm install
```

### 5. Palaid Laravel serveri

Pirmajā terminālī:

```bash
php artisan serve --host=0.0.0.0
```

### 6. Palaid Vite

Otrajā terminālī:

```bash
npm run dev -- --host 0.0.0.0
```

GitHub Codespaces vidē lietotni atver caur **Ports** sadaļas 8000. porta saiti.

Abiem serveriem jādarbojas, kamēr izstrādes vidē lieto lietotni.

### Noderīgas komandas

```bash
php artisan route:list
php artisan route:list --path=api
php artisan migrate:status
php artisan test
npm run build
```

## 8. API maršruti

Zemāk uzskaitīti projektā identificētie galapunkti. To piekļuves nosacījumi un HTTP metodes jāapstiprina ar pašreizējo `php artisan route:list --path=api` rezultātu.

| Metode   | Galapunkts                     | Pielietojums                          |
| -------- | ------------------------------ | ------------------------------------- |
| GET      | `/api/user`                    | Lietotāja informācija                 |
| GET      | `/api/profile`                 | Profils un dalība                     |
| GET      | `/api/plans`                   | Dalības plāni                         |
| GET      | `/api/locations`               | Aktīvās lokācijas                     |
| POST     | `/api/plans/purchase`          | Dalības iegāde                        |
| GET      | `/api/friends`                 | Draugu informācija                    |
| POST     | `/api/friends`                 | Draudzības pieprasījums               |
| GET      | `/api/friends/search`          | Lietotāju meklēšana                   |
| PATCH    | `/api/friends/{friendship}`    | Draudzības pieprasījuma atjaunināšana |
| DELETE   | `/api/friends/{friendship}`    | Draudzības saites dzēšana             |
| GET      | `/api/progress`                | Progresa dati                         |
| GET/POST | `/api/body-metrics`            | Ķermeņa rādītāji                      |
| GET/POST | `/api/qr-code`                 | QR koda funkcionalitāte               |
| GET      | `/api/reports`                 | Pārskatu dati                         |
| GET      | `/api/admin/reports`           | Administratora pārskati               |
| GET      | `/api/workouts`                | Treniņu vēsture                       |
| POST     | `/api/workouts`                | Treniņa saglabāšana                   |
| GET      | `/api/workout-templates`       | Treniņu veidnes                       |
| GET      | `/api/calorie-profile`         | Kaloriju profila dati                 |
| POST     | `/api/calorie-profile`         | Kaloriju profila saglabāšana          |
| POST     | `/api/workout-recommendations` | Treniņu ieteikumi                     |
| GET      | `/api/admin/dashboard`         | Administratora informācijas panelis   |

## 9. Testēšana

Projektā ir `tests/Feature/` mape. Tajā ir arī `AdminUserManagementTest.php`, kas paredzēts administratora lietotāju pārvaldības funkcionalitātes testēšanai.

Testus var palaist ar:

```bash
php artisan test
```

Testu faktiskie rezultāti un testu datubāzes konfigurācija jāpārbauda izstrādes vidē.

## 10. Git un GitHub

Projekts tiek pārvaldīts ar Git, un galvenais izstrādes zars ir `main`.

Lai apskatītu izmaiņas:

```bash
git status
git diff
```

Lai saglabātu un augšupielādētu izmaiņas:

```bash
git add <FAILA_CELS>
git commit -m "Describe your changes"
git push origin main
```

Pirms izmaiņu commit pārbaudi, vai netiek iekļauti `.env` faili, paroles, piekļuves tokeni vai citi sensitīvi dati.

SQLite datubāzes iekļaušana GitHub jāveic apzināti: tā var saturēt lokālos lietotāju kontus un citus datus. Komandas biedriem ir jāzina, vai jāizmanto koplietojamā datubāzes kopija vai katram sava lokālā datubāze.

## 11. Ieteicamā iepazīšanās secība jaunam izstrādātājam

1. Izlasīt šo `README.md`.
2. Izpētīt `routes/web.php` un `php artisan route:list`.
3. Izpētīt `resources/js/router/index.js`, lai saprastu frontend lapu struktūru.
4. Atvērt attiecīgos Vue komponentus mapē `resources/js/pages/`.
5. Izpētīt `app/Models/` un `app/Http/Controllers/`.
6. Izlasīt `database/migrations/` un `database/seeders/`.
7. Palaist lietotni un pārbaudīt galvenās funkcijas.
8. Palaist testus un pārbaudīt to rezultātus.

## 12. Svarīgas piezīmes

Šī dokumentācija balstīta uz pašreiz identificēto projekta struktūru un apskatītajiem koda fragmentiem. Tā nav automātiski pārbaudīta visu funkciju specifikācija.

Pirms projekta nodošanas citam izstrādātājam papildini šo dokumentāciju ar:

* precīzām PHP, Laravel, Node.js un npm versijām;
* autentifikācijas un administratora tiesību aprakstu;
* pilnu datubāzes tabulu un attiecību shēmu;
* katras funkcijas biznesa loģiku;
* testu rezultātiem;
* drošu testa kontu izveides instrukciju, nepublicējot paroles vai reālus lietotāju datus.
