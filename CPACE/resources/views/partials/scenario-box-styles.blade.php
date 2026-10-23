.sc-group { position:relative; }
.sc-box { background:#fff; border:1.5px solid #ead9d9; border-left:4px solid var(--primary, #7B1D1D); border-radius:14px; margin:0 0 14px; overflow:hidden; text-align:left; }
.sc-head { width:100%; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 14px; border:0; background:#fbf4f4; color:var(--primary, #7B1D1D); font:600 11.5px 'Poppins',sans-serif; letter-spacing:.05em; text-transform:uppercase; cursor:pointer; }
.sc-head .sc-sub { font-weight:500; letter-spacing:0; text-transform:none; color:#a07b7b; margin-left:8px; }
.sc-head .sc-chev { font-size:10px; transition:transform .2s; }
.sc-body { padding:14px 16px; font-size:14px; line-height:1.65; color:#222; overflow-wrap:anywhere; font-weight:400; text-transform:none; letter-spacing:0; }
.sc-box.collapsed .sc-body { display:none; }
.sc-box.collapsed .sc-chev { transform:rotate(180deg); }
.sc-box.sticky { position:sticky; top:var(--sc-top, 8px); z-index:6; box-shadow:0 6px 18px rgba(20,22,40,.10); }
.sc-box.sticky .sc-body { max-height:36vh; overflow:auto; }
