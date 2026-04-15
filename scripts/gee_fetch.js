const ee = require('@google/earthengine');

// Mengambil kredensial dari Environment Variables
const privateKey = process.env.GEE_PRIVATE_KEY ? process.env.GEE_PRIVATE_KEY.replace(/\\n/g, '\n') : '';
const clientEmail = process.env.GEE_CLIENT_EMAIL || '';

if (!privateKey || !clientEmail) {
    console.log(JSON.stringify({ 
        status: 'error', 
        message: 'Kredensial GEE (GEE_CLIENT_EMAIL / GEE_PRIVATE_KEY) tidak ditemukan di konfigurasi env.'
    }));
    process.exit(1);
}

const credentials = {
    client_email: clientEmail,
    private_key: privateKey
};

const authenticate = () => new Promise((resolve, reject) => {
    ee.data.authenticateViaPrivateKey(credentials, resolve, reject);
});

const initialize = () => new Promise((resolve, reject) => {
    ee.initialize(null, null, resolve, reject);
});

// 3. Menarik Data Satelit Sungguhan (Contoh: NDVI Kesehatan Padi)
const getSatelliteData = () => {
    return new Promise((resolve, reject) => {
        try {
            // Titik koordinat sawah petani (Contoh: Area Sumedang)
            // Nanti angka ini bisa dinamis dikirim dari parameter chat/GPS HP Petani
            const longitude = parseFloat(process.env.GEE_LONGITUDE || '108.0886');
            const latitude = parseFloat(process.env.GEE_LATITUDE || '-6.8403');
            const startDate = process.env.GEE_START_DATE || '2026-02-01';
            const endDate = process.env.GEE_END_DATE || '2026-04-15';
            const point = ee.Geometry.Point([longitude, latitude]);

            // Mengambil gambar satelit Sentinel-2 terbaru (2 bulan terakhir)
            const image = ee.ImageCollection('COPERNICUS/S2_SR_HARMONIZED')
                .filterBounds(point)
                .filterDate(startDate, endDate)
                .sort('CLOUDY_PIXEL_PERCENTAGE') // Cari yang paling tidak tertutup awan
                .first();

            // Menghitung NDVI (Indeks Vegetasi)
            // Formula: (NIR - Red) / (NIR + Red) -> (Band 8 - Band 4)
            const ndvi = ee.Image(image).normalizedDifference(['B8', 'B4']).rename('NDVI');

            // Menarik nilai rata-rata pixel di titik kordinat tersebut (skala 10 meter)
            const ndviValue = ndvi.reduceRegion({
                reducer: ee.Reducer.first(),
                geometry: point,
                scale: 10
            });

            // Mengevaluasi hasil dari server Google ke Node.js secara asinkron
            ndviValue.evaluate((result, error) => {
                if (error) {
                    reject(error);
                } else {
                    resolve({
                        satellite: 'COPERNICUS/S2_SR_HARMONIZED',
                        coordinates: {
                            longitude,
                            latitude
                        },
                        window: {
                            start: startDate,
                            end: endDate
                        },
                        data: result
                    });
                }
            });
        } catch (e) {
            reject(e);
        }
    });
};

(async () => {
    try {
        // Proses Otentikasi dan Inisialisasi GEE
        await authenticate();
        await initialize();

        // Panggil fungsi penarik data
        const dataLahan = await getSatelliteData();

        console.log(JSON.stringify({
            status: 'success',
            lokasi: 'Sumedang',
            data: dataLahan
        }));
        process.exit(0);
    } catch (error) {
        console.log(JSON.stringify({ status: 'error', message: error.toString() }));
        process.exit(1);
    }
})();
