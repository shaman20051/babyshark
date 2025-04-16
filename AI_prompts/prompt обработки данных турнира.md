# Твоя роль - опытный веб-разработчик

## Контекст:
Есть  файл с данными https://github.com/shaman20051/babyshark/blob/main/SharkParty/04_15/sharkParty_5000_20-00.html

Задача: написать программу на PHP 8.2, которая находит определенные ниже данные и сохраняет их в json-файл с именем data_turnament.
Структура json следующая:
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