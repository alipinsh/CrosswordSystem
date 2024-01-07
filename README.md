# Krustvārdu mīklu sistēma

## Projekta apraksts
Izstrādātā sistēma ir vietne, kur lietotāji var veidot un dalīties ar savām krustvārdu mīklām. Citi lietotāji var risināt mīklas, komentēt tos un atzīmēt par patīkamam.

## Izmantotās tehnoloģijas
- HTML, CSS, Javascript
- PHP 8.2
- Codeigniter 4
- MySQL

## Izmantotie avoti
[divpusher/codeigniter4-auth](https://github.com/divpusher/codeigniter4-auth) - autentifikācijas koda bāze

[String length in bytes in JavaScript](https://stackoverflow.com/a/5515960) - JS funkcija, lai uzzinātu simbolu virkni izmēru baitos

[Codeigniter 4 User Guide](https://codeigniter.com/user_guide/index.html) - Codeigniter 4 dokumentācija

## Uzstādīšanas instrukcijas
Lokāla instalācija:
1. Uz datora jābūt PHP 8.2 un MySQL
2. Klonēt šo repozitoriju
3. Atvērt projekta sakni
4. `composer install`
5. Veidot .env failu projekta saknē un ierakstīt:
  ```
  CI_ENVIRONMENT = development
  app.baseURL = 'http://localhost:8080/'
  database.default.hostname = localhost
  database.default.database = {datubāzes nosaukums}
  database.default.username = {datubāzes lietotājs}
  database.default.password = {datubāzes parole}
  database.default.DBDriver = MySQLi
  ```
6. `php spark migrate`
7. Rekomendējams izmantot [Mailtrap](https://mailtrap.io/) servisu un redīģēt app/Config/Mail.php, lai izmantot testa e-pastus.
8. `php spark run`
