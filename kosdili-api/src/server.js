// src/server.js
const express = require('express');
const cors    = require('cors');
require('dotenv').config();

const app = express();

// ── Middleware global ──────────────────────────────
// Izinkan request dari frontend (React di localhost:5173)
app.use(cors({
  origin: [
    'http://localhost:5173',       // React dev
    'https://kosdili.vercel.app'   // Production
  ],
  credentials: true
}));

// Parsing JSON body — seperti json_decode() di PHP
app.use(express.json());

// ── Import semua route ─────────────────────────────
const authRoute     = require('./routes/auth');
const propertiRoute = require('./routes/properti');
const bookingRoute  = require('./routes/booking');
const ulasanRoute   = require('./routes/ulasan');

// ── Daftarkan route dengan prefix URL ─────────────
app.use('/api/auth',     authRoute);
app.use('/api/properti', propertiRoute);
app.use('/api/booking',  bookingRoute);
app.use('/api/ulasan',   ulasanRoute);

// ── Route cek server hidup ─────────────────────────
app.get('/', (req, res) => {
  res.json({ status: 'OK', app: 'KosDili API', versi: '1.0.0' });
});

// ── Error handler global ───────────────────────────
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({ error: 'Terjadi kesalahan server' });
});

// ── Jalankan server ────────────────────────────────
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server KosDili berjalan di port ${PORT}`);
});