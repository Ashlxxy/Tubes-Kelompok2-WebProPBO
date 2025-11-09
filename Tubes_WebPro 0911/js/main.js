// ======= Mock Auth & Role =======
const ADMIN_EMAIL = "admin@ukmband.telkom";
const ADMIN_PASS = "admin123"; // demo only

function save(key, value){ localStorage.setItem(key, JSON.stringify(value)); }
function load(key, fallback){ try{ return JSON.parse(localStorage.getItem(key)) ?? fallback; } catch(e){ return fallback; } }

function currentUser(){ return load("auth_user", null); }
function setUser(u){ save("auth_user", u); }
function isAdmin(){ const u = currentUser(); return u && u.role === "admin"; }

function login(e){
  e.preventDefault();
  const email = document.getElementById('loginEmail').value.trim();
  const pass = document.getElementById('loginPassword').value;

  if(email === ADMIN_EMAIL && pass === ADMIN_PASS){
    setUser({name:"Administrator", email, role:"admin"});
    window.location.href = "index.html";
    return false;
  }
  const users = load('users', []);
  const found = users.find(u => u.email === email && u.password === pass);
  if(found){
    setUser({name:found.name, email:found.email, role:"user"});
    window.location.href = "index.html";
  }else{
    alert("Email atau password salah.");
  }
  return false;
}

function register(e){
  e.preventDefault();
  const name = document.getElementById('regName').value.trim();
  const email = document.getElementById('regEmail').value.trim();
  const password = document.getElementById('regPassword').value;

  const users = load('users', []);
  if(users.some(u => u.email === email)){
    alert('Email sudah terdaftar.');
    return false;
  }
  users.push({name, email, password});
  save('users', users);
  alert('Registrasi berhasil. Silakan login.');
  window.location.href = "login.html";
  return false;
}

function logout(){
  localStorage.removeItem('auth_user');
  window.location.href = "login.html";
}

// ======= Seed Data =======
const defaultSongs = [
  {
    id: 's1',
    title: "Lust",
    artist: "Bachelor's Thrill",
    desc: "Energi eksplosif dan riff cepat yang menggambarkan kebebasan mahasiswa.",
    cover: "assets/img/c1.jpg",
    file: "assets/songs/Lust.wav",
    plays: 0,
    likes: 0,
    likedBy: [],
    comments: []
  },
  {
    id: 's2',
    title: "Form",
    artist: "Coral",
    desc: "Eksperimen suara yang menggambarkan bentuk dan warna bawah laut.",
    cover: "assets/img/c2.jpg",
    file: "assets/songs/coral_form.wav",
    plays: 0,
    likes: 0,
    likedBy: [],
    comments: []
  },
  {
    id: 's3',
    title: "Strangled",
    artist: "Dystopia",
    desc: "Nuansa gelap yang menggambarkan kekacauan batin dan tekanan sosial.",
    cover: "assets/img/c3.jpg",
    file: "assets/songs/Strangled.wav",
    plays: 0,
    likes: 0,
    likedBy: [],
    comments: []
  },
  {
    id: 's4',
    title: "Revoir",
    artist: "Elisya_au",
    desc: "Balada melankolis tentang perpisahan dan kenangan yang tak terlupakan.",
    cover: "assets/img/c4.jpg",
    file: "assets/songs/revoir.wav",
    plays: 0,
    likes: 0,
    likedBy: [],
    comments: []
  },
  {
    id: 's5',
    title: "Prisoner",
    artist: "Secrets.",
    desc: "Karya eksperimental dengan pesan tentang kebebasan dan rahasia terdalam.",
    cover: "assets/img/c5.jpg",
    file: "assets/songs/Prisoner.wav",
    plays: 0,
    likes: 0,
    likedBy: [],
    comments: []
  },
  {
    id: 's6',
    title: "Langit Kelabu",
    artist: "The Harper",
    desc: "Harmoni lembut dengan lirik puitis tentang hujan dan harapan.",
    cover: "assets/img/c6.jpg",
    file: "assets/songs/Langit Kelabu.wav",
    plays: 0,
    likes: 0,
    likedBy: [],
    comments: []
  },
  {
    id: 's7',
    title: "The Overtrain - New World",
    artist: "The Overtrain",
    desc: "Irama cepat dengan semangat membangun dunia baru yang lebih baik.",
    cover: "assets/img/c7.jpg",
    file: "assets/songs/NewWorld.wav",
    plays: 0,
    likes: 0,
    likedBy: [],
    comments: []
  }
];

function ensureSeed(){
  if(!localStorage.getItem('songs')){
    save('songs', defaultSongs);
  }
  if(!localStorage.getItem('playlists')){
    save('playlists', {});
  }
  if(!localStorage.getItem('feedbacks')){
    save('feedbacks', []);
  }
  if(!currentUser()){
    const pathname = location.pathname.split('/').pop();
    if(!['login.html', 'register.html', ''].includes(pathname)){
      window.location.href = 'login.html';
    }
  }
}
ensureSeed();

// ======= Navbar state =======
function initNavbar(){
  const user = currentUser();
  const label = document.getElementById('navUserLabel');
  const adminLink = document.getElementById('adminLink');
  if(label && user){ label.textContent = user.name + (isAdmin() ? ' (Admin)' : ''); }
  if(adminLink){ adminLink.style.display = isAdmin() ? 'block' : 'none'; }
}
document.addEventListener('DOMContentLoaded', initNavbar);

// ======= Helpers =======
function songCardTemplate(song){
  return `
    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
      <div class="card song p-2 h-100" onclick="openSong('${song.id}')">
        <img src="${song.cover}" class="cover w-100 mb-2" alt="${song.title}">
        <div class="d-flex flex-column">
          <div class="fw-semibold text-truncate">${song.title}</div>
          <div class="small text-dark-300 text-truncate">${song.artist}</div>
          <div class="mt-2 d-flex align-items-center gap-2">
            <span class="badge badge-soft"><i class="bi bi-play-fill"></i> ${song.plays}</span>
            <span class="badge bg-accent-soft"><i class="bi bi-heart-fill"></i> ${song.likes}</span>
          </div>
        </div>
      </div>
    </div>
  `;
}

function renderSongGrid(targetId, list){
  const el = document.getElementById(targetId);
  if(!el) return;
  el.innerHTML = list.map(songCardTemplate).join('');
}

// ======= Home Page =======
function initHome() {
  const pathname = location.pathname.split('/').pop();
  if (pathname !== 'index.html' && pathname !== '') return;

  const songs = load('songs', []);
  if (!songs || songs.length === 0) return;

  // === HERO (lagu terbaru) ===
  const hero = songs[songs.length - 1];
  if (hero) {
    const ht = document.getElementById('heroTitle');
    const ha = document.getElementById('heroArtist');
    if (ht) ht.textContent = hero.title;
    if (ha) ha.textContent = hero.artist;

    // tampilkan cover di hero section
    const heroCard = document.querySelector('.hero-card .ratio');
    if (heroCard) {
      heroCard.innerHTML = `
        <img src="${hero.cover}" 
             alt="${hero.title}" 
             class="w-100 h-100 object-fit-cover rounded-3 fade-in">
      `;
    }
  }

  // === MOST POPULAR ===
  const popular = [...songs]
    .sort((a, b) => (b.likes + b.plays) - (a.likes + a.plays))
    .slice(0, 6);
  renderSongGrid('popularSongs', popular);

  // === BOOKLET (mini deskripsi lagu) ===
  const bookletEl = document.getElementById('bookletCards');
  if (bookletEl) {
    bookletEl.innerHTML = songs
      .map(
        (s) => `
        <div class="col-md-6 col-lg-4">
          <div class="card song p-3 h-100">
            <div class="d-flex align-items-start gap-3">
              <img src="${s.cover}" width="96" height="96" class="rounded-3 object-fit-cover" alt="${s.title}">
              <div class="flex-fill">
                <div class="fw-semibold">${s.title}</div>
                <div class="small text-dark-300 mb-2">${s.artist}</div>
                <p class="small text-dark-200 mb-2">${s.desc}</p>
                <button class="btn btn-sm btn-outline-accent" onclick="openSong('${s.id}')">
                  <i class="bi bi-play-fill me-1"></i>Putar
                </button>
              </div>
            </div>
          </div>
        </div>
      `
      )
      .join('');
  }

  // === SEMUA LAGU ===
  renderSongGrid('songList', songs);
}
document.addEventListener('DOMContentLoaded', initHome);

// ======= Routing to Song Detail =======
function openSong(id){
  window.location.href = 'song-detail.html?id=' + encodeURIComponent(id);
}

// ======= Song Detail Logic =======
function getQueryParam(name){
  const url = new URL(window.location.href);
  return url.searchParams.get(name);
}

function addHistory(song){
  const user = currentUser();
  if(!user) return;
  const key = 'history_' + user.email;
  const arr = load(key, []);
  arr.unshift({id:song.id, title:song.title, artist:song.artist, time:new Date().toISOString()});
  save(key, arr.slice(0,100));
}

function initSongDetail(){
  const pathname = location.pathname.split('/').pop();
  if(pathname !== 'song-detail.html') return;
  const id = getQueryParam('id');
  const songs = load('songs', []);
  const song = songs.find(s=>s.id===id) || songs[0];
  if(!song) return;

  // increment plays + history
  song.plays += 1;
  save('songs', songs);
  addHistory(song);

  const wrap = document.getElementById('songDetailWrapper');
  wrap.innerHTML = `
    <div class="col-lg-4">
      <img src="${song.cover}" class="song-detail-cover w-100" alt="${song.title}">
    </div>
    <div class="col-lg-8">
      <div class="song-detail-box">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div>
            <h2 class="mb-1">${song.title}</h2>
            <div class="text-dark-300">${song.artist}</div>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-outline-accent btn-like" id="btnLike"><i class="bi bi-heart"></i> <span id="likeCount">${song.likes}</span></button>
            <button class="btn btn-outline-accent" id="btnAddToPlaylist"><i class="bi bi-plus-circle"></i> Tambah Playlist</button>
          </div>
        </div>
        <p class="mt-3 mb-3 text-dark-200">${song.desc}</p>
        <audio id="player" controls class="w-100">
          <source src="${song.file || ''}" type="audio/mpeg">
        </audio>
        <div class="mt-2 d-flex gap-2">
          <button class="btn btn-sm btn-outline-accent" id="btnRepeat"><i class="bi bi-arrow-repeat"></i> Repeat</button>
          <button class="btn btn-sm btn-outline-accent" id="btnPrev"><i class="bi bi-skip-backward"></i></button>
          <button class="btn btn-sm btn-outline-accent" id="btnNext"><i class="bi bi-skip-forward"></i></button>
        </div>
      </div>
    </div>
  `;

  // actions
  document.getElementById('btnLike').onclick = () => {
  const user = currentUser();
  if (!user) {
    alert('Silakan login terlebih dahulu.');
    return;
  }

  // pastikan ada array likedBy
  if (!song.likedBy) song.likedBy = [];

  const userLiked = song.likedBy.includes(user.email);

  if (userLiked) {
    // kalau udah like → unlike
    song.likes = Math.max(0, song.likes - 1);
    song.likedBy = song.likedBy.filter(e => e !== user.email);
    document.getElementById('btnLike').classList.remove('active');
  } else {
    // kalau belum like → like
    song.likes += 1;
    song.likedBy.push(user.email);
    document.getElementById('btnLike').classList.add('active');
  }

  // simpan perubahan
  save('songs', songs);
  document.getElementById('likeCount').textContent = song.likes;
};

  document.getElementById('btnAddToPlaylist').onclick = () => addToPlaylistPrompt(song.id);
  document.getElementById('btnRepeat').onclick = () => {
    const p = document.getElementById('player');
    p.loop = !p.loop;
    alert(p.loop ? 'Repeat aktif' : 'Repeat nonaktif');
  };
  document.getElementById('btnNext').onclick = () => navigateSong(1, song.id);
  document.getElementById('btnPrev').onclick = () => navigateSong(-1, song.id);

  // comments ui
  renderComments(song.id);
  const send = document.getElementById('commentSend');
  const input = document.getElementById('commentInput');
  send.onclick = ()=>{
    const user = currentUser();
    if(!user){ alert('Silakan login.'); return; }
    const val = input.value.trim();
    if(!val) return;
    song.comments.push({user:user.name, text:val});
    save('songs', songs);
    input.value='';
    renderComments(song.id);
  };
}
document.addEventListener('DOMContentLoaded', initSongDetail);

function navigateSong(step, currentId){
  const songs = load('songs', []);
  const idx = songs.findIndex(s=>s.id===currentId);
  const nextIdx = (idx + step + songs.length) % songs.length;
  openSong(songs[nextIdx].id);
}

// ======= Comments (shared) =======
function renderComments(songId){
  const songs = load('songs', []);
  const s = songs.find(x=>x.id===songId);
  const list = document.getElementById('commentList');
  if(!list) return;
  list.innerHTML = (s?.comments||[]).map(c=>`
    <div class="list-group-item list-group-item-dark mb-2 rounded">
      <div class="small text-dark-200"><i class="bi bi-person"></i> ${c.user}</div>
      <div>${c.text}</div>
    </div>
  `).join('') || '<div class="text-dark-300">Belum ada komentar.</div>';
}

// ======= Playlist =======
function userPlaylists(){
  const user = currentUser();
  const all = load('playlists', {});
  if(!user) return [];
  if(!all[user.email]) all[user.email] = [];
  save('playlists', all);
  return all[user.email];
}

function saveUserPlaylists(arr){
  const user = currentUser();
  const all = load('playlists', {});
  all[user.email] = arr;
  save('playlists', all);
}

function createPlaylist(){
  const name = prompt('Nama playlist:');
  if(!name) return;
  const pls = userPlaylists();
  pls.push({name, ids:[]});
  saveUserPlaylists(pls);
  renderPlaylists();
}

function addToPlaylistPrompt(songId){
  const pls = userPlaylists();
  if(pls.length===0){ alert('Buat playlist dulu ya.'); return; }
  const choice = prompt('Tambah ke playlist mana? \n' + pls.map((p,i)=>`${i+1}. ${p.name}`).join('\n'));
  const idx = parseInt(choice)-1;
  if(isNaN(idx) || idx<0 || idx>=pls.length) return;
  if(!pls[idx].ids.includes(songId)) pls[idx].ids.push(songId);
  saveUserPlaylists(pls);
  alert('Ditambahkan ke playlist.');
}

function renderPlaylists(){
  const pathname = location.pathname.split('/').pop();
  if(pathname !== 'playlist.html') return;
  const container = document.getElementById('playlistContainer');
  const pls = userPlaylists();
  const songs = load('songs', []);

  container.innerHTML = pls.map((p,pi)=>{
    const items = p.ids.map(id=> songs.find(s=>s.id===id)).filter(Boolean);
    return `
      <div class="col-12">
        <div class="card-dark p-3 rounded-3">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">${p.name}</h5>
            <div class="btn-group">
              <button class="btn btn-sm btn-outline-accent" onclick="renamePlaylist(${pi})"><i class="bi bi-pencil"></i></button>
              <button class="btn btn-sm btn-outline-accent" onclick="deletePlaylist(${pi})"><i class="bi bi-trash"></i></button>
            </div>
          </div>
          <div class="row mt-3 g-3">
            ${items.map(s=>`
              <div class="col-6 col-md-3">
                <div class="card song p-2 h-100" onclick="openSong('${s.id}')">
                  <img src="${s.cover}" class="cover w-100 mb-2" alt="${s.title}">
                  <div class="fw-semibold text-truncate">${s.title}</div>
                  <div class="small text-dark-300 text-truncate">${s.artist}</div>
                  <button class="btn btn-sm btn-outline-accent mt-2" onclick="removeFromPlaylist(event, ${pi}, '${s.id}')"><i class="bi bi-x-circle"></i> Hapus</button>
                </div>
              </div>
            `).join('')}
          </div>
        </div>
      </div>
    `;
  }).join('') || '<div class="text-dark-300">Belum ada playlist.</div>';
}
document.addEventListener('DOMContentLoaded', renderPlaylists);

function renamePlaylist(i){
  const pls = userPlaylists();
  const name = prompt('Ubah nama playlist:', pls[i].name);
  if(!name) return;
  pls[i].name = name;
  saveUserPlaylists(pls);
  renderPlaylists();
}
function deletePlaylist(i){
  const pls = userPlaylists();
  if(!confirm('Hapus playlist?')) return;
  pls.splice(i,1);
  saveUserPlaylists(pls);
  renderPlaylists();
}
function removeFromPlaylist(ev, i, id){
  ev.stopPropagation();
  const pls = userPlaylists();
  pls[i].ids = pls[i].ids.filter(x=>x!==id);
  saveUserPlaylists(pls);
  renderPlaylists();
}

// ======= History =======
function renderHistory(){
  const pathname = location.pathname.split('/').pop();
  if(pathname !== 'history.html') return;
  const user = currentUser();
  const key = 'history_' + (user?.email || '');
  const list = load(key, []);
  const box = document.getElementById('historyList');
  box.innerHTML = list.map((h,i)=>`
    <a class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center" href="#" onclick="openSong('${h.id}')">
      <div><i class="bi bi-music-note-beamed"></i> ${h.title} <span class="text-dark-300">— ${h.artist}</span></div>
      <small class="text-dark-300">${new Date(h.time).toLocaleString()}</small>
    </a>
  `).join('') || '<div class="text-dark-300">Belum ada riwayat.</div>';
}
document.addEventListener('DOMContentLoaded', renderHistory);

// ======= Feedback =======
function submitFeedback(e){
  e.preventDefault();
  const name = document.getElementById('fbName').value.trim();
  const email = document.getElementById('fbEmail').value.trim();
  const message = document.getElementById('fbMessage').value.trim();
  const arr = load('feedbacks', []);
  arr.unshift({name,email,message,time:new Date().toISOString()});
  save('feedbacks', arr);
  document.querySelector('form').reset();
  renderFeedbackList();
  alert('Feedback terkirim (mock).');
  return false;
}

function renderFeedbackList(){
  const pathname = location.pathname.split('/').pop();
  if(pathname !== 'feedback.html') return;
  const arr = load('feedbacks', []);
  const box = document.getElementById('feedbackList');
  box.innerHTML = arr.map(f=>`
    <div class="list-group-item list-group-item-dark">
      <div class="d-flex justify-content-between">
        <strong>${f.name}</strong>
        <small class="text-dark-300">${new Date(f.time).toLocaleString()}</small>
      </div>
      <div class="small text-dark-300">${f.email}</div>
      <div>${f.message}</div>
    </div>
  `).join('') || '<div class="text-dark-300">Belum ada feedback.</div>';
}
document.addEventListener('DOMContentLoaded', renderFeedbackList);

// ======= Admin page =======
function initAdmin(){
  const pathname = location.pathname.split('/').pop();
  if(pathname !== 'admin.html') return;
  if(!isAdmin()){
    alert('Khusus admin.');
    window.location.href = 'index.html';
    return;
  }
  renderAdminTable();
  renderAdminFeedback();
}
document.addEventListener('DOMContentLoaded', initAdmin);

function renderAdminTable(){
  const tbody = document.querySelector('#adminSongTable tbody');
  const songs = load('songs', []);
  tbody.innerHTML = songs.map((s,i)=>`
    <tr>
      <td>${i+1}</td>
      <td><img src="${s.cover}" width="48" class="rounded"></td>
      <td><a class="link-accent" href="song-detail.html?id=${s.id}">${s.title}</a></td>
      <td>${s.artist}</td>
      <td>${s.plays}</td>
      <td>${s.likes}</td>
      <td>
        <div class="btn-group btn-group-sm">
          <button class="btn btn-outline-accent" onclick="adminEdit('${s.id}')"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-outline-accent" onclick="adminDelete('${s.id}')"><i class="bi bi-trash"></i></button>
        </div>
      </td>
    </tr>
  `).join('') || '<tr><td colspan="7" class="text-center text-dark-300">Belum ada lagu.</td></tr>';
}

function renderAdminFeedback(){
  const list = document.getElementById('adminFeedbackList');
  const arr = load('feedbacks', []);
  list.innerHTML = arr.map(f=>`
    <div class="list-group-item list-group-item-dark">
      <div class="d-flex justify-content-between">
        <strong>${f.name}</strong>
        <small class="text-dark-300">${new Date(f.time).toLocaleString()}</small>
      </div>
      <div class="small text-dark-300">${f.email}</div>
      <div>${f.message}</div>
    </div>
  `).join('') || '<div class="text-dark-300">Belum ada feedback.</div>';
}

// ======= Admin CRUD =======
function adminEdit(id){
  const s = load('songs', []).find(x=>x.id===id);
  if(!s) return;
  document.getElementById('crudTitle').textContent = 'Edit Lagu';
  document.getElementById('songId').value = s.id;
  document.getElementById('songTitle').value = s.title;
  document.getElementById('songArtist').value = s.artist;
  document.getElementById('songDesc').value = s.desc;
  document.getElementById('songCover').value = s.cover;
  document.getElementById('songFile').value = s.file;
  new bootstrap.Modal(document.getElementById('songCrudModal')).show();
}

function adminDelete(id){
  if(!confirm('Hapus lagu ini?')) return;
  let songs = load('songs', []);
  songs = songs.filter(s=>s.id!==id);
  save('songs', songs);
  renderAdminTable();
}

function adminSaveSong(e){
  e.preventDefault();
  let songs = load('songs', []);
  const id = document.getElementById('songId').value || ('s' + Math.random().toString(36).slice(2,7));
  const payload = {
    id,
    title: document.getElementById('songTitle').value.trim(),
    artist: document.getElementById('songArtist').value.trim(),
    desc: document.getElementById('songDesc').value.trim(),
    cover: document.getElementById('songCover').value.trim() || 'assets/img/default-cover.jpg',
    file: document.getElementById('songFile').value.trim() || '',
    plays: 0,
    likes: 0,
    comments: []
  };
  const idx = songs.findIndex(s=>s.id===id);
  if(idx>=0){ songs[idx] = payload; } else { songs.push(payload); }
  save('songs', songs);
  renderAdminTable();
  bootstrap.Modal.getInstance(document.getElementById('songCrudModal')).hide();
  return false;
}

// ======= Search =======
function handleGlobalSearch(e){
  e.preventDefault();
  const q = document.getElementById('globalSearch').value.toLowerCase();
  const songs = load('songs', []);
  const filtered = songs.filter(s => s.title.toLowerCase().includes(q) || s.desc.toLowerCase().includes(q) || s.artist.toLowerCase().includes(q));
  if(location.pathname.endsWith('index.html') || location.pathname.endsWith('/')){
    renderSongGrid('songList', filtered);
  }else{
    window.location.href = 'index.html#q=' + encodeURIComponent(q);
  }
  return false;
}

// Load filter from hash
window.addEventListener('load', ()=>{
  const hash = decodeURIComponent(location.hash || '');
  if(hash.startsWith('#q=')){
    const q = hash.slice(3).toLowerCase();
    const songs = load('songs', []);
    const filtered = songs.filter(s => s.title.toLowerCase().includes(q) || s.desc.toLowerCase().includes(q) || s.artist.toLowerCase().includes(q));
    const input = document.getElementById('globalSearch');
    if(input) input.value = q;
    const list = document.getElementById('songList');
    if(list) renderSongGrid('songList', filtered);
  }
});
