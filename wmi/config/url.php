<?php
/**
 * @author WhoAmI
 * @date   2019-07-24
 */

return [
    'portal'            => ['c' => 'Portal', 'm' => 'index'],
    'wallet'            => ['c' => 'WalletControl', 'm' => 'index'],
    'wallet/(\d+)/(.+)' => ['c' => 'WalletControl', 'm' => 'index'],
    'admin/.*'          => ['c' => 'Portal', 'm' => 'index'],
    'status'            => ['c' => 'StatusControl', 'm' => 'index'],
];