<template>
  <section class="tournament" v-if="data.info">
    <h1 class="tournament__title">Турнир "{{ data.info.name }}"</h1>

    <div class="tournament__info">
      <div class="tournament__info-item" v-for="(value, label) in infoItems" :key="label">
        <span class="tournament__info-label">{{ label }}:</span>
        <span class="tournament__info-value">{{ value }}</span>
      </div>
    </div>

    <div class="tournament__tabs">
      <div class="tournament__tabs-header">
        <button
          class="tournament__tab-btn"
          :class="{ 'tournament__tab-btn--active': activeTab === 'info' }"
          @click="activeTab = 'info'"
        >Инфо</button>
        <button
          class="tournament__tab-btn"
          :class="{ 'tournament__tab-btn--active': activeTab === 'prizes' }"
          @click="activeTab = 'prizes'"
        >Призы</button>
        <button
          class="tournament__tab-btn"
          :class="{ 'tournament__tab-btn--active': activeTab === 'blinds' }"
          @click="activeTab = 'blinds'"
        >Блайнды</button>
        <button
          class="tournament__tab-btn"
          :class="{ 'tournament__tab-btn--active': activeTab === 'players' }"
          @click="activeTab = 'players'"
        >Игроки</button>
      </div>

      <div class="tournament__tabs-content">
        <div v-if="activeTab === 'info'" class="tournament__tab tournament__tab--active">
          <div class="tournament__data">
            <div class="tournament__data-row" v-for="(value, key) in data.info" :key="key">
              <div class="tournament__data-key">{{ key }}</div>
              <div class="tournament__data-value">{{ value }}</div>
            </div>
          </div>
        </div>

        <div v-if="activeTab === 'prizes'" class="tournament__tab tournament__tab--active">
          <div class="tournament__prizes-table">
            <div class="tournament__table-row tournament__table-row--header">
              <div class="tournament__table-col">Место</div>
              <div class="tournament__table-col">Приз ($)</div>
            </div>
            <div class="tournament__table-row" v-for="prize in data.prize" :key="prize.place">
              <div class="tournament__table-col">{{ prize.place }}</div>
              <div class="tournament__table-col">{{ prize.prize.toFixed(2) }}</div>
            </div>
          </div>
        </div>

        <div v-if="activeTab === 'blinds'" class="tournament__tab tournament__tab--active">
          <div class="tournament__blinds-table">
            <div class="tournament__table-row tournament__table-row--header">
              <div class="tournament__table-col">Уровень</div>
              <div class="tournament__table-col">Блайнды</div>
              <div class="tournament__table-col">Анте</div>
              <div class="tournament__table-col">Время (мин)</div>
              <div class="tournament__table-col">Тайм-банк</div>
            </div>
            <div class="tournament__table-row" v-for="blind in data.blinds" :key="blind.level">
              <div class="tournament__table-col">{{ blind.level }}</div>
              <div class="tournament__table-col">{{ blind.blind }}</div>
              <div class="tournament__table-col">{{ blind.ante }}</div>
              <div class="tournament__table-col">{{ blind.mins }}</div>
              <div class="tournament__table-col">{{ blind.timebank }}</div>
            </div>
          </div>
        </div>

        <div v-if="activeTab === 'players'" class="tournament__tab tournament__tab--active">
          <div class="tournament__filters" style="margin-bottom: 1rem; display: flex; gap: 1rem; align-items: center;">
            <label>
              Страна:
              <select v-model="selectedCountry">
                <option value="all">Все</option>
                <option v-for="country in countries" :key="country" :value="country">{{ country }}</option>
              </select>
            </label>
            <label>
              Игроков на странице:
              <select v-model="playersPerPage">
                <option :value="10">10</option>
                <option :value="20">20</option>
                <option :value="50">50</option>
              </select>
            </label>
          </div>

          <div class="tournament__players-table">
            <div class="tournament__table-row tournament__table-row--header">
              <div class="tournament__table-col">Место</div>
              <div class="tournament__table-col">Игрок</div>
              <div class="tournament__table-col">Страна</div>
              <div class="tournament__table-col">Результат</div>
            </div>
            <div class="tournament__table-row" v-for="player in paginatedPlayers" :key="player.rank">
              <div class="tournament__table-col">{{ player.rank }}</div>
              <div class="tournament__table-col">
                <div class="tournament__player">
                  <img :src="player.player.avatar" :alt="player.player.nickname" class="tournament__player-avatar">
                  <span class="tournament__player-name">{{ player.player.nickname }}</span>
                </div>
              </div>
              <div class="tournament__table-col">{{ player.player.country.toUpperCase() }}</div>
              <div class="tournament__table-col">{{ player.chips }}</div>
            </div>
          </div>

          <div class="pagination">
            <button
              v-for="page in totalPages"
              :key="page"
              @click="currentPage = page"
              class="pagination__link"
              :class="{ 'pagination__link--active': currentPage === page }"
            >
              {{ page }}
            </button>
          </div>
        </div>

      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

const data = ref({})
const activeTab = ref('info')
const infoItems = ref({})
const selectedCountry = ref('all')
const playersPerPage = ref(10)
const currentPage = ref(1)

onMounted(async () => {
  const response = await fetch('/data/data_turnament_example.json')
  data.value = await response.json()
  infoItems.value = {
    'Старт': new Date(data.value.startTime).toLocaleString('ru-RU'),
    'Гарантия': `$${data.value.info.prizePool.toLocaleString()}`,
    'Бай-ин': `$${data.value.info.buyIn}`,
    'Статус': data.value.status.trim(),
    'Длительность': data.value.duration
  }
})

const countries = computed(() => {
  const set = new Set(data.value.players?.map(p => p.player.country.toUpperCase()))
  return Array.from(set || [])
})

const filteredPlayers = computed(() => {
  if (!data.value.players) return []
  return selectedCountry.value === 'all'
    ? data.value.players
    : data.value.players.filter(p => p.player.country.toUpperCase() === selectedCountry.value)
})

const paginatedPlayers = computed(() => {
  const start = (currentPage.value - 1) * playersPerPage.value
  return filteredPlayers.value.slice(start, start + playersPerPage.value)
})

const totalPages = computed(() => {
  return Math.ceil(filteredPlayers.value.length / playersPerPage.value)
})
</script>

<style scoped>
.tournament__player {
  display: flex;
  align-items: center;
  gap: 10px;
}

.tournament__player-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-top: 20px;
}

.pagination__link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 4px;
  background-color: #f0f0f0;
  color: #333;
  transition: background-color 0.2s ease, color 0.2s ease;
}

.pagination__link:hover {
  background-color: #ffa726;
  color: white;
}

.pagination__link--active {
  background-color: #ffa726;
  color: white;
  font-weight: bold;
}
</style>
