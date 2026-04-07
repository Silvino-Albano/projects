// const express = require('express');
// const router = express.Router();
// router.get('/', (req, res) => res.json({ message: 'Properti Route OK' }));
// module.exports = router;
const express = require('express');
const router  = express.Router();

// Placeholder — akan diisi setelah auth berhasil
router.get('/', (req, res) => {
  res.json({ message: 'Properti API — segera hadir' });
});

module.exports = router;