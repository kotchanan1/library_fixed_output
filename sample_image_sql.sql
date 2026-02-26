-- sample_image_sql.sql
-- ตัวอย่างคำสั่ง SQL ที่ใช้เพิ่ม/อัพเดตรูปหนังสือโดยใช้ BLOB
-- ก่อนใช้งานควรตรวจสอบว่าตาราง books มีคอลัมน์ต่อไปนี้แล้ว:
--   book_image        VARCHAR(255)     (เก็บ path เรียกใช้ได้ตามเดิม)
--   book_image_blob   LONGBLOB        (เก็บข้อมูลไบนารีของรูป)
--   book_image_mime   VARCHAR(50)     (เก็บชนิด MIME เช่น image/jpeg)

-- 1. อัพเดตรูปโดยใช้ LOAD_FILE (ไฟล์ต้องอยู่บนเครื่อง MySQL และ
--    อยู่ในโฟลเดอร์ที่อนุญาตของ secure_file_priv)
UPDATE books
SET
    book_image_blob = LOAD_FILE('/absolute/path/to/uploads/books/foo.jpg'),
    book_image_mime = 'image/jpeg'
WHERE book_id = 123;

-- 2. เพิ่มหนังสือใหม่พร้อมรูป
INSERT INTO books (book_name,type_name,author,status,book_image,book_image_blob,book_image_mime)
VALUES (
    'ทดสอบ',
    'นิยาย',
    'ผู้แต่งตัวอย่าง',
    'available',
    NULL, -- ถ้าไม่ต้องการเก็บ path ด้วย
    LOAD_FILE('/absolute/path/to/uploads/books/bar.png'),
    'image/png'
);

-- 3. ใช้ literal hex/base64 หากไฟล์อยู่ที่เครื่องอื่นหรือไม่สามารถใช้ LOAD_FILE
--    ให้แปลงรูปเป็น hex แล้วใช้แบบนี้ (ตัวอย่างสั้น ๆ ทดสอบเท่านั้น):
-- UPDATE books
-- SET book_image_blob = 0xFFD8FFE0, book_image_mime='image/jpeg'
-- WHERE book_id = 456;

-- หมายเหตุ:
-- * ชื่อไฟล์ (foo.jpg, bar.png) ไม่จำเป็นต้องสัมพันธ์กับ book_id
-- * หากคุณต้องการ export ข้อมูล BLOB จากเครื่องสัมคมาถึงอีกเครื่อง ให้
--   รัน SELECT แล้วนำค่า HEX/BASE64 ไปเติมใน INSERT/UPDATE เช่นด้านบน
-- * โฟลเดอร์ "uploads/books" ควรถูกคัดลอกไปยังเซิร์ฟเวอร์ปลายทางด้วย
--   เพื่อให้ path เดิมยังใช้งานได้สำหรับรูปที่เก็บเป็นไฟล์
