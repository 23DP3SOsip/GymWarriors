# GymWarriors
Trello:
https://trello.com/invite/b/6a9911e19ee62f7a2983ce29/ATTIc923f27b940afdb263b096b354b5124c4122CE5D/my

Google Docs:
https://docs.google.com/document/d/1oEPkM7nhTVBrXZoFuMKZvvzTzmru_mypJKF636SYJiE/edit?usp=sharing

Canva:
https://canva.link/1q0ip1cmtbxqaz7

## Reports

Authenticated users can open `/reports` to view their payment and visit
history, summary cards, date/location filters, and charts.

Users with the `admin` role see all members' payments and visits. Promote a
user locally with:

```powershell
php artisan tinker --execute="App\Models\User::where('email', 'admin@example.com')->update(['role' => 'admin']);"
```