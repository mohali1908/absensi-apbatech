// Ambil elemen modal dengan ID 'absen-detail-modal'
const absenDetailModal = document.getElementById("absen-detail-modal");

// Tambahkan event listener ketika modal akan ditampilkan
absenDetailModal.addEventListener("show.bs.modal", async (event) => {
    // Ambil elemen yang memicu modal (tombol)
    const badgeTrigger = event.relatedTarget;

    // Ambil absenId dari atribut data-absen-id
    const { absenId } = badgeTrigger.dataset;

    // Ambil elemen-elemen dalam modal yang akan diisi dengan data
    const absenNotesModal = absenDetailModal.querySelector("#absen-notes");

    // Tampilkan pesan loading saat menunggu data dari API
    absenNotesModal.textContent = "Loading...";

    try {
        // Lakukan fetch ke API untuk mengambil data absen berdasarkan absenId
        const res = await fetch(absenUrl + "?id=" + absenId);

        // Jika respon tidak OK (misalnya status 404 atau 500), lempar error
        if (!res.ok) {
            throw new Error("Error fetching data");
        }

        // Ubah respon ke dalam format JSON
        const data = await res.json();

        // Isi elemen-elemen modal dengan data yang diambil dari API
        absenNotesModal.textContent = data.notes || "No notes available";
    } catch (error) {
        // Tampilkan pesan error jika ada masalah
        absenNotesModal.textContent = "Failed to load data";
        console.error("Error fetching absence data:", error);
    }
});