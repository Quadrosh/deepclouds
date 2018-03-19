<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace console\controllers;

use common\models\RolesAssignment;

use common\rbac\AdminRule;
use common\rbac\CreatorRule;
use common\rbac\StatRule;
use yii\console\Controller;

class RbacController extends Controller {

    public function actionIndex() {

        $oldAssigns = RolesAssignment::find()->all();
//        $oldAssigns = null;

        $auth = \Yii::$app->authManager;
        $auth->removeAll();


//        создание ролей
        $admin = $auth->createRole( 'Admin' );
        $stat = $auth->createRole( 'Stat' );
        $creator = $auth->createRole( 'Creator' );


        $auth->add( $admin );
        $auth->add( $stat );
        $auth->add( $creator );



        // правило
        $statRule = new StatRule();
        $auth->add( $statRule );
        // Разрешение
        $statPermission = $auth->createPermission('statPermission');
        $statPermission->description = 'Доступ к статистике - просмотр и изменение';
        $statPermission->ruleName = $statRule->name;
        $auth->add( $statPermission );

        $adminRule = new AdminRule();
        $auth->add( $adminRule );
        $adminPermission = $auth->createPermission('adminPermission');
        $adminPermission->description = 'Доступ к админке';
        $adminPermission->ruleName = $statRule->name;
        $auth->add( $adminPermission );

        $creatorRule = new CreatorRule();
        $auth->add( $creatorRule );
        $creatorPermission = $auth->createPermission('creatorPermission');
        $creatorPermission->description = 'Доступ ко всему';
        $creatorPermission->ruleName = $statRule->name;
        $auth->add( $creatorPermission );


//        наследование
        $auth->addChild( $creator, $creatorPermission );
        $auth->addChild( $creator, $admin );
        $auth->addChild( $admin, $adminPermission );
        $auth->addChild( $admin, $stat );
        $auth->addChild( $stat, $statPermission );



//        $auth->assign($admin,1);
//        $auth->assign($admin,3);
//        $auth->assign($stat,5);
//        $auth->assign($creator,4);

        if (is_array($oldAssigns)) {
            foreach ($oldAssigns as $oldAssign) {
                $auth->assign($auth->createRole($oldAssign['item_name']),$oldAssign['user_id']);
            }
        }

    }
}

// запуск из консоли php yii rbac