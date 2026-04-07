// const express = require('express');
// const router = express.Router();
// router.get('/', (req, res) => res.json({ message: 'Ulasan Route OK' }));
// module.exports = router;
const express = require('express');
const router  = express.Router();

// Placeholder — akan diisi di fase berikutnya
router.get('/', (req, res) => {
  res.json({ message: 'Ulasan API — segera hadir' });
});

module.exports = router;