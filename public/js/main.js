/**
 * ============================================
 * DADOS DAS SALAS
 * ============================================
 */

const SALAS = [
    { id: 'A001', name: 'Sala A001', icon: '🏛️', info: 'Piso 0' },
    { id: 'A002', name: 'Sala A002', icon: '🏛️', info: 'Piso 0' },
    { id: 'B101', name: 'Sala B101', icon: '💻', info: 'Laboratório' },
    { id: 'B102', name: 'Sala B102', icon: '💻', info: 'Laboratório' },
    { id: 'C201', name: 'Sala C201', icon: '📚', info: 'Piso 1' },
    { id: 'C202', name: 'Sala C202', icon: '📚', info: 'Piso 1' },
];

/**
 * ============================================
 * GERADOR DE HORÁRIOS (Dados de Exemplo)
 * ============================================
 */

function gerarHorarios() {
    const dados = {};
    const hoje = new Date();
    const dia = hoje.getDay();
    const diff = dia === 0 ? -6 : 1 - dia;
    const segunda = new Date(hoje);
    segunda.setDate(hoje.getDate() + diff);

    SALAS.forEach(sala => {
        dados[sala.id] = {};
        for (let i = 0; i < 5; i++) {
            const data = new Date(segunda);
            data.setDate(segunda.getDate() + i);
            const dataStr = data.toISOString().split('T')[0];

            const eventos = [];
            const num = Math.floor(Math.random() * 4);
            const tipos = ['horario', 'horario', 'horario', 'exame', 'avaliacao'];
            const nomes = ['Programação Web', 'Bases de Dados', 'Redes', 'Matemática', 'Inglês', 'Projeto', 'SO'];

            for (let j = 0; j < num; j++) {
                const h = 8 + Math.floor(Math.random() * 8);
                const m = Math.random() > 0.5 ? '00' : '30';
                const hf = h + 1 + Math.floor(Math.random() * 2);
                const mf = Math.random() > 0.5 ? '00' : '30';
                eventos.push({
                    time: `${String(h).padStart(2, '0')}:${m} - ${String(hf).padStart(2, '0')}:${mf}`,
                    name: nomes[Math.floor(Math.random() * nomes.length)],
                    type: tipos[Math.floor(Math.random() * tipos.length)]
                });
            }
            dados[sala.id][dataStr] = eventos;
        }
    });
    return dados;
}

// GERAR OS HORÁRIOS
const HORARIOS = gerarHorarios();

/**
 * ============================================
 * FUNÇÕES AUXILIARES
 * ============================================
 */

function getWeekStart(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1);
    d.setDate(diff);
    d.setHours(0, 0, 0, 0);
    return d;
}

function formatDate(date) {
    return `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
}

function formatDateISO(date) {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showPage(id) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}

/**
 * ============================================
 * LISTA DE SALAS (CORRIGIDO)
 * ============================================
 */

function renderRooms() {
    const container = document.getElementById('roomsContainer');
    if (!container) {
        console.error('❌ Elemento roomsContainer não encontrado!');
        return;
    }

    console.log('✅ Renderizando salas...');

    let html = '';
    SALAS.forEach(room => {
        html += `
            <div class="room-card" data-id="${room.id}">
                <div class="room-icon">${room.icon}</div>
                <div class="room-name">${escapeHtml(room.name)}</div>
                <div class="room-info">${escapeHtml(room.info)}</div>
            </div>
        `;
    });

    container.innerHTML = html;
    console.log(`✅ ${SALAS.length} salas renderizadas!`);

    // Adicionar eventos de clique
    document.querySelectorAll('.room-card').forEach(card => {
        card.addEventListener('click', function(e) {
            const roomId = this.dataset.id;
            console.log(`🖱️ Clicou na sala: ${roomId}`);
            openSchedule(roomId);
        });
    });
}

/**
 * ============================================
 * HORÁRIO
 * ============================================
 */

let currentRoom = null;
let currentWeek = getWeekStart(new Date());

function openSchedule(roomId) {
    console.log(`📅 Abrindo horário da sala: ${roomId}`);
    currentRoom = roomId;
    const room = SALAS.find(r => r.id === roomId);
    document.getElementById('roomTitle').textContent = `📍 ${room ? room.name : 'Sala'}`;
    showPage('pageSchedule');
    loadSchedule();
}

function loadSchedule() {
    const container = document.getElementById('daysContainer');
    const label = document.getElementById('weekLabel');

    if (!container || !label) {
        console.error('❌ Elementos do horário não encontrados!');
        return;
    }

    const end = new Date(currentWeek);
    end.setDate(end.getDate() + 6);
    label.textContent = `${formatDate(currentWeek)} - ${formatDate(end)}`;

    container.innerHTML = '<div class="loading">⏳ A carregar horário...</div>';

    setTimeout(() => {
        const dias = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta'];
        const hoje = new Date();
        const hojeStr = formatDateISO(hoje);
        let html = '';

        for (let i = 0; i < 5; i++) {
            const data = new Date(currentWeek);
            data.setDate(data.getDate() + i);
            const dataStr = formatDateISO(data);
            const isToday = dataStr === hojeStr;
            const events = (HORARIOS[currentRoom] && HORARIOS[currentRoom][dataStr]) || [];

            html += `
                <div class="day-card ${isToday ? 'current-day' : ''}">
                    <div class="day-header">
                        <span class="day-name">${dias[i]}</span>
                        <span class="day-date">${formatDate(data)}</span>
                        <span class="event-count">${events.length}</span>
                    </div>
            `;

            if (events.length === 0) {
                html += `<div class="empty-state"><p>Sem eventos</p></div>`;
            } else {
                events.forEach(e => {
                    const labels = { horario: 'Horário', exame: 'Exame', avaliacao: 'Avaliação' };
                    html += `
                        <div class="event type-${e.type}">
                            <div class="event-time">⏰ ${escapeHtml(e.time)}</div>
                            <div class="event-name">${escapeHtml(e.name)}</div>
                            <span class="event-type">${labels[e.type] || e.type}</span>
                        </div>
                    `;
                });
            }
            html += `</div>`;
        }

        container.innerHTML = html;
        console.log('✅ Horário carregado!');
    }, 300);
}

/**
 * ============================================
 * INICIALIZAÇÃO
 * ============================================
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 App iniciada!');
    
    // Renderizar lista de salas
    renderRooms();

    // ===== Navegação Semanal =====
    const prevBtn = document.getElementById('prevWeek');
    const nextBtn = document.getElementById('nextWeek');
    const backBtn = document.getElementById('backButton');

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            console.log('⬅️ Semana anterior');
            currentWeek.setDate(currentWeek.getDate() - 7);
            loadSchedule();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            console.log('➡️ Próxima semana');
            currentWeek.setDate(currentWeek.getDate() + 7);
            loadSchedule();
        });
    }

    if (backBtn) {
        backBtn.addEventListener('click', function() {
            console.log('🔙 Voltando às salas');
            showPage('pageRooms');
        });
    }

    // ===== Swipe (corrigido - sem conflito com scroll) =====
    let touchStartX = 0;
    let touchStartY = 0;
    let isSwiping = false;

    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
        isSwiping = false;
    }, { passive: true });

    document.addEventListener('touchmove', function(e) {
        const currentX = e.changedTouches[0].screenX;
        const currentY = e.changedTouches[0].screenY;
        const deltaX = Math.abs(currentX - touchStartX);
        const deltaY = Math.abs(currentY - touchStartY);

        if (deltaX > deltaY && deltaX > 10) {
            isSwiping = true;
        } else if (deltaY > deltaX && deltaY > 10) {
            isSwiping = false;
        }
    }, { passive: true });

    document.addEventListener('touchend', function(e) {
        if (!isSwiping) return;
        
        const pageSchedule = document.getElementById('pageSchedule');
        if (!pageSchedule || !pageSchedule.classList.contains('active')) return;

        const touchEndX = e.changedTouches[0].screenX;
        const touchEndY = e.changedTouches[0].screenY;
        const diffX = touchStartX - touchEndX;
        const diffY = Math.abs(touchStartY - touchEndY);

        if (Math.abs(diffX) > diffY && Math.abs(diffX) > 30) {
            if (diffX > 0) {
                if (nextBtn) nextBtn.click();
            } else {
                if (prevBtn) prevBtn.click();
            }
        }

        isSwiping = false;
    }, { passive: true });

    console.log('✅ App pronta!');
    console.log(`📋 ${SALAS.length} salas disponíveis`);
    console.log('💡 Clique numa sala para ver o horário');
});