<?php

namespace Magenest\PaymentEPay\Model\Config\Source;

class Bank
{
    protected $bankLists = [
        'TPBM' => 'Ngân hàng TMCP Tiên Phong',
        'HDBM' => 'Ngân hàng TMCP Phát Triển Thành Phố Hồ Chí Minh',
        'GPBM' => 'Ngân hàng TM TNHH MTV Dầu Khí Toàn Cầu',
        'TCBM' => 'Ngân hàng TMCP Kỹ thương Việt Nam',
        'OJBM' => 'Ngân hàng TMCP Đại Dương',
        'BVBM' => 'Ngân hàng TMCP Bảo Việt',
        'MBKM' => 'Ngân hàng TMCP Quân Đội',
        'VPBM' => 'Ngân hàng TMCP Việt Nam Thịnh Vương',
        'VTBM' => 'Ngân hàng TMCP Công Thương Việt Nam',
        'EIBM' => 'Ngân hàng TMCP Xuất nhập khẩu Việt Nam',
        'SEAM' => 'Ngân hàng TMCP Đông Nam Á',
        'SCBM' => 'Ngân hàng TMCP Sài Gòn',
        'OCBM' => 'Ngân hàng TMCP Phương Đông',
        'ABBM' => 'Ngân hàng TMCP An Bình',
        'MSBM' => 'Ngân hàng TMCP Hàng Hải Việt Nam',
        'VABM' => 'Ngân hàng TMCP Việt Á',
        'NCBM' => 'Ngân hàng TMCP Quốc Dân',
        'BIDM' => 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam',
        'SHBM' => 'Ngân hàng TMCP Sài Gòn - Hà Nội',
        'DABM' => 'Ngân hàng TMCP Đông Á',
        'VIBM' => 'Ngân hàng TMCP Quốc Tế',
        'IVBM' => 'Ngân hàng TNHH Indovina',
        'VCCM' => 'Ngân hàng  TMCP Bản Việt',
        'KLBM' => 'Ngân hàng  TMCP Kiên Long',
        'PGBM' => 'Ngân hàng  TMCP Xăng Dầu Petrolimex',
        'VARM' => 'Ngân hàng Nông Nghiệp và Phát triển Nông Thôn Việt Nam',
        'STBM' => 'Ngân hàng TMCP Sài Gòn Thương Tín',
        'PVCM' => 'Ngân hàng TMCP Đại Chúng Việt Nam',
        'VRBM' => 'Ngân hàng Liên Doanh Việt Nga',
        'NABM' => 'Ngân hàng TMCP Nam Á',
        'LPBM' => 'Ngân hàng TMCP Bưu Điện Liên Việt',
        'VCBM' => 'Ngân hàng TMCP Ngoại thương Việt Nam',
        'ACBM' => 'Ngân hàng TMCP Á Châu',
        'NASM' => 'Ngân hàng TMCP Bắc Á',
        'WRBM' => 'Ngân Hàng TNHH MTV Woori Việt Nam',
        'UOBM' => 'Ngân hàng TNHH MTV United Overseas Bank',
        'SGBM' => 'Ngân hàng TMCP Sài gòn Công thương',
        'SVBM' => 'Ngân Hàng Shinhan Việt Nam',
        'PBVN' => 'Ngân hàng TNHH MTV Public Việt Nam',
        'VBKM' => 'Ngân hàng TNHH MTV Public Việt Nam',
        'FECM' => 'Thẻ đơn vị tài chính FE Credit',
        'HMCM' => 'Thẻ đơn vị tài chính Home Credit'
    ];

    public function getBankName($code)
    {
        return $this->bankLists[strtoupper($code)] ?? "N/A";
    }
}
