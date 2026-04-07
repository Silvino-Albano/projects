// const express = require('express');
// const router = express.Router();
// router.get('/', (req, res) => res.json({ message: 'Route OK' }));
// module.exports = router;


// src/routes/auth.js
const express  = require('express');
const bcrypt   = require('bcrypt');
const jwt      = require('jsonwebtoken');
const db       = require('../config/db');
const { verifyToken } = require('../middleware/auth');

const router = express.Router();

// ─────────────────────────────────────────────────────
// POST /api/auth/register
// Daftarkan user baru (pencari, pemilik, atau admin)
// ─────────────────────────────────────────────────────
router.post('/register', async (req, res) => {
  try {
    const { nama, email, telepon, password, peran } = req.body;

    // Validasi input wajib
    if (!nama || !email || !password) {
      return res.status(400).json({ error: 'Nama, email, dan password wajib diisi' });
    }

    // Cek apakah email sudah terdaftar
    const existing = await db.query(
      'SELECT id FROM users WHERE email = $1', [email]
    );
    if (existing.rows.length > 0) {
      return res.status(409).json({ error: 'Email sudah terdaftar' });
    }

    // Hash password — seperti password_hash() di PHP
    const hash = await bcrypt.hash(password, 10);

    // Tentukan peran — default 'pencari', admin tidak bisa daftar sendiri
    const roleValid = ['pencari', 'pemilik'];
    const userPeran = roleValid.includes(peran) ? peran : 'pencari';

    // Simpan ke database
    const result = await db.query(
      `INSERT INTO users (nama, email, telepon, password_hash, peran)
       VALUES ($1, $2, $3, $4, $5)
       RETURNING id, nama, email, peran, dibuat_pada`,
      [nama, email, telepon, hash, userPeran]
    );

    const user = result.rows[0];

    // Buat JWT token langsung setelah register
    const token = jwt.sign(
      { id: user.id, peran: user.peran },
      process.env.JWT_SECRET,
      { expiresIn: process.env.JWT_EXPIRES }
    );

    res.status(201).json({
      message: 'Registrasi berhasil',
      token,
      user
    });

  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Gagal mendaftarkan akun' });
  }
});

// ─────────────────────────────────────────────────────
// POST /api/auth/login
// ─────────────────────────────────────────────────────
router.post('/login', async (req, res) => {
  try {
    const { email, password } = req.body;

    if (!email || !password) {
      return res.status(400).json({ error: 'Email dan password wajib diisi' });
    }

    // Cari user berdasarkan email
    const result = await db.query(
      'SELECT * FROM users WHERE email = $1', [email]
    );

    if (result.rows.length === 0) {
      return res.status(401).json({ error: 'Email atau password salah' });
    }

    const user = result.rows[0];

    // Bandingkan password — seperti password_verify() di PHP
    const cocok = await bcrypt.compare(password, user.password_hash);
    if (!cocok) {
      return res.status(401).json({ error: 'Email atau password salah' });
    }

    // Buat JWT token
    const token = jwt.sign(
      { id: user.id, peran: user.peran },
      process.env.JWT_SECRET,
      { expiresIn: process.env.JWT_EXPIRES }
    );

    // Jangan kembalikan password_hash ke client
    const { password_hash, ...userData } = user;

    res.json({
      message: 'Login berhasil',
      token,
      user: userData
    });

  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Gagal login' });
  }
});

// ─────────────────────────────────────────────────────
// GET /api/auth/me
// Ambil data user yang sedang login (butuh token)
// ─────────────────────────────────────────────────────
router.get('/me', verifyToken, async (req, res) => {
  try {
    const result = await db.query(
      'SELECT id, nama, email, telepon, peran, dibuat_pada FROM users WHERE id = $1',
      [req.user.id]
    );
    res.json({ user: result.rows[0] });
  } catch (err) {
    res.status(500).json({ error: 'Gagal ambil data user' });
  }
});

module.exports = router;
