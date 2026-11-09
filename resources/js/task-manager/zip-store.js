/*
    A small zip writer for files that are already compressed (PNG, JPG). Every
    entry is stored as-is rather than deflated, so it needs no library.
    Task Manager's "Download & Mark as Complete" uses it to bundle ID cards.
*/

const CRC_TABLE = (() => {
    const table = new Uint32Array(256);
    for (let n = 0; n < 256; n++) {
        let c = n;
        for (let k = 0; k < 8; k++) c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
        table[n] = c >>> 0;
    }
    return table;
})();

function crc32(bytes) {
    let crc = 0xffffffff;
    for (let i = 0; i < bytes.length; i++) crc = CRC_TABLE[(crc ^ bytes[i]) & 0xff] ^ (crc >>> 8);
    return (crc ^ 0xffffffff) >>> 0;
}

/** MS-DOS time and date fields, which is what zip headers store. */
function dosStamp(date) {
    return {
        time: (date.getHours() << 11) | (date.getMinutes() << 5) | Math.floor(date.getSeconds() / 2),
        day: ((Math.max(date.getFullYear(), 1980) - 1980) << 9) | ((date.getMonth() + 1) << 5) | date.getDate(),
    };
}

/**
 * @param {{ name: string, data: Uint8Array }[]} files
 * @param {Date} [date] modified time written for every entry
 * @returns {Blob} an application/zip blob
 */
export function zipStore(files, date = new Date()) {
    const encoder = new TextEncoder();
    const { time, day } = dosStamp(date);
    const body = [];
    const directory = [];
    let offset = 0;

    files.forEach(({ name, data }) => {
        const nameBytes = encoder.encode(name);
        const crc = crc32(data);

        const local = new DataView(new ArrayBuffer(30));
        local.setUint32(0, 0x04034b50, true); // local file header
        local.setUint16(4, 20, true); // version needed (2.0)
        local.setUint16(6, 0x0800, true); // names are UTF-8
        local.setUint16(8, 0, true); // stored, no compression
        local.setUint16(10, time, true);
        local.setUint16(12, day, true);
        local.setUint32(14, crc, true);
        local.setUint32(18, data.length, true);
        local.setUint32(22, data.length, true);
        local.setUint16(26, nameBytes.length, true);
        local.setUint16(28, 0, true);
        body.push(local, nameBytes, data);

        const entry = new DataView(new ArrayBuffer(46));
        entry.setUint32(0, 0x02014b50, true); // central directory header
        entry.setUint16(4, 20, true); // made by
        entry.setUint16(6, 20, true); // version needed
        entry.setUint16(8, 0x0800, true);
        entry.setUint16(10, 0, true);
        entry.setUint16(12, time, true);
        entry.setUint16(14, day, true);
        entry.setUint32(16, crc, true);
        entry.setUint32(20, data.length, true);
        entry.setUint32(24, data.length, true);
        entry.setUint16(28, nameBytes.length, true);
        entry.setUint32(42, offset, true); // where the local header starts
        directory.push(entry, nameBytes);

        offset += 30 + nameBytes.length + data.length;
    });

    const directorySize = directory.reduce((sum, part) => sum + part.byteLength, 0);
    const end = new DataView(new ArrayBuffer(22));
    end.setUint32(0, 0x06054b50, true); // end of central directory
    end.setUint16(8, files.length, true);
    end.setUint16(10, files.length, true);
    end.setUint32(12, directorySize, true);
    end.setUint32(16, offset, true);

    return new Blob([...body, ...directory, end], { type: "application/zip" });
}
