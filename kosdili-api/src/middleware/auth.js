// src/middleware/auth.js
const jwt = require('jsonwebtoken');

// ── Middleware 1: cek apakah user sudah login ──────
const verifyToken = (req, res, next) => {
  // Ambil token dari header Authorization
  // Format: "Bearer eyJhbGci..."
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1];

  if (!token) {
    return res.status(401).json({ error: 'Token tidak ditemukan, silakan login' });
  }

  try {
    // Verifikasi token — kalau expired atau palsu, langsung error
    const decoded = jwt.verify(token, process.env.JWT_SECRET);

    // Simpan data user ke req agar bisa dipakai di route
    req.user = decoded; // { id, peran, iat, exp }
    next();             // lanjut ke route handler
  } catch (err) {
    return res.status(403).json({ error: 'Token tidak valid atau sudah expired' });
  }
};

// ── Middleware 2: cek role user ────────────────────
// Pemakaian: checkRole('admin') atau checkRole('pemilik')
const checkRole = (...roles) => {
  return (req, res, next) => {
    if (!roles.includes(req.user.peran)) {
      return res.status(403).json({
        error: `Akses ditolak. Hanya untuk: ${roles.join(', ')}`
      });
    }
    next();
  };
};

module.exports = { verifyToken, checkRole };