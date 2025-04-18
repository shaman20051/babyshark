# Твоя роль - опытный веб-разработчик

## Контекст:
Есть 3 html файл с данными, которые лежат в одной папке.

Задача: написать программу на PHP 8.2, которая находит определенные ниже данные и сохраняет их в json-файл с именем data_turnament.
Структура json следующая (значения ключей - для примера):
{
  "tournamentName": "SharkParty Weekly",
  "startTime": "2025-04-20T19:00:00+03:00",
  "status": "регистрация",
  "info": {
    "prizePool": 5000,
    "registered": 143,
    "reEntered": 24,
    "inTheMoney": 35,
    "name": "SharkParty",
    "game": "NL Holdem",
    "start": "2025-04-20T19:00:00+03:00",
    "lateRegistration": "40 mins",
    "buyIn": 25,
    "reEntry": 25,
    "startingChips": 10000,
    "requirements": "None",
    "club": "SharkParty",
    "blinds": "L37(40,000/80,000 Ante10,500)",
    "reBuy": "Not Available",
    "addOn": "Not Available",
    "anteType": "Standard Ante",
    "blindInterval": "4mins",
    "onBreak": "5 Minute Break On the Hour",
    "timeBank": "15s x 2",
    "minMaxPlayers": "5~10,000",
    "playersPerTable": 9,
    "network": "MTT Network",
    "contract": "0x598D217...3C78",
    "rules": "О дополнительных призах за участие в турнирах можно узнать в телеграм-канале: https://t.me/sh"
  },
  "prize": [
    {
      "place": 1,
      "prize": 900
    },
    {
      "place": 2,
      "prize": 800
    },
    {
      "place": 3,
      "prize": 700
    },
    {
      "place": 4,
      "prize": 600
    }
  ],
  "blinds": [
    {
      "lv": 1,
      "blinds": "50/100",
      "ante": 15,
      "timeBank": "15s x 2",
      "mins": 4
    },
    {
      "lv": 2,
      "blinds": "60/120",
      "ante": 30,
      "timeBank": "-",
      "mins": 4
    },
    {
      "lv": 3,
      "blinds": "70/140",
      "ante": 45,
      "timeBank": "-",
      "mins": 4
    },
    {
      "lv": 4,
      "blinds": "80/160",
      "ante": 65,
      "timeBank": "15s x 2",
      "mins": 4
    }
  ],
  "players": [
    {
      "rank": 1,
      "player": "Dammy",
      "chips": 30000
    },
    {
      "rank": 2,
      "player": "Sony",
      "chips": 23000
    },
    {
      "rank": 3,
      "player": "Peter",
      "chips": 300
    },
    {
      "rank": 4,
      "player": "SaSA",
      "chips": "Finished"
    }
  ]
}

Можешь ли самостоятельно найти нужные данные и подставить их в json средствами PHP?
## Уточнение по исходным данным:
- данные для "info" берутся из файла, в конце имени которого содержится "_start"
- данные для "prize" и "players" берутся из файла, в конце имени которого содержится "_finish"
- данные для "blinds" берутся из файла, в конце имени которого содержится "_blinds"