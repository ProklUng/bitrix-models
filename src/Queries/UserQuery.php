<?php

namespace Arrilot\BitrixModels\Queries;

use Illuminate\Support\Collection;
use Arrilot\BitrixModels\Models\UserModel;

/**
 * @method UserQuery active()
 * @method UserQuery fromGroup($groupId)
 */
class UserQuery extends OldCoreQuery
{
    /**
     * Query sort.
     *
     * @var array
     */
    public $sort = ['last_name' => 'asc'];

    /**
     * List of standard entity fields.
     *
     * @var array
     */
    protected $standardFields = [
        'ID',
        'IS_ONLINE',
        'LAST_ACTIVITY_DATE',
        'AUTO_TIME_ZONE',
        'TIME_ZONE',
        'CONFIRM_CODE',
        'STORED_HASH',
        'EXTERNAL_AUTH_ID',
        'LOGIN_ATTEMPTS',
        'CHECKWORD',
        'CHECKWORD_TIME',
        'DATE_REGISTER',
        'TIMESTAMP_X',
        'LAST_LOGIN',
        'ACTIVE',
        'BLOCKED',
        'TITLE',
        'NAME',
        'LAST_NAME',
        'SECOND_NAME',
        'EMAIL',
        'LOGIN',
        'PHONE_NUMBER',
        'PASSWORD',
        'XML_ID',
        'LID',
        'LANGUAGE_ID',
        'PERSONAL_PROFESSION',
        'PERSONAL_WWW',
        'PERSONAL_ICQ',
        'PERSONAL_GENDER',
        'PERSONAL_BIRTHDAY',
        'PERSONAL_PHOTO',
        'PERSONAL_PHONE',
        'PERSONAL_FAX',
        'PERSONAL_MOBILE',
        'PERSONAL_PAGER',
        'PERSONAL_COUNTRY',
        'PERSONAL_STATE',
        'PERSONAL_CITY',
        'PERSONAL_ZIP',
        'PERSONAL_STREET',
        'PERSONAL_MAILBOX',
        'PERSONAL_NOTES',
        'WORK_COMPANY',
        'WORK_WWW',
        'WORK_DEPARTMENT',
        'WORK_POSITION',
        'WORK_PROFILE',
        'WORK_LOGO',
        'WORK_PHONE',
        'WORK_FAX',
        'WORK_PAGER',
        'WORK_COUNTRY',
        'WORK_STATE',
        'WORK_CITY',
        'WORK_ZIP',
        'WORK_STREET',
        'WORK_MAILBOX',
        'WORK_NOTES',
        'ADMIN_NOTES',
    ];

    /**
     * Get the collection of users according to the current query.
     *
     * @return Collection
     */
    protected function loadModels()
    {
        $queryType = 'UserQuery::getList';
        $sort = $this->sort;
        $filter = $this->normalizeFilter();
        $params = [
            'SELECT'     => $this->propsMustBeSelected() ? ['UF_*'] : ($this->normalizeUfSelect() ?: false),
            'NAV_PARAMS' => $this->navigation,
            'FIELDS'     => $this->normalizeSelect(),
        ];
        $selectGroups = $this->groupsMustBeSelected();
        $keyBy = $this->keyBy;

        $callback = function() use ($sort, $filter, $params, $selectGroups){
            $users = [];
            $rsUsers = $this->bxObject->getList($sort, $sortOrder = false, $filter, $params);
            while ($arUser = $this->performFetchUsingSelectedMethod($rsUsers)) {
                if ($selectGroups) {
                    $arUser['GROUP_ID'] = $this->bxObject->getUserGroup($arUser['ID']);
                }
        
                $this->addItemToResultsUsingKeyBy($users, new $this->modelName($arUser['ID'], $arUser));
            }
    
            return new Collection($users);
        };

        return $this->handleCacheIfNeeded(compact('queryType', 'sort', 'filter', 'params', 'selectGroups', 'keyBy'), $callback);
    }

    /**
     * Get the first user with a given login.
     *
     * @param string $login
     *
     * @return UserModel
     */
    public function getByLogin($login)
    {
        $this->filter['LOGIN_EQUAL_EXACT'] = $login;

        return $this->first();
    }

    /**
     * Get the first user with a given email.
     *
     * @param string $email
     *
     * @return UserModel
     */
    public function getByEmail($email)
    {
        $this->filter['EMAIL'] = $email;

        return $this->first();
    }

    /**
     * Get count of users according the current query.
     *
     * @return int
     */
    public function count()
    {
        if ($this->queryShouldBeStopped) {
            return 0;
        }

        $queryType = 'UserQuery::count';
        $filter = $this->normalizeFilter();
        $callback = function() use ($filter) {
            return (int) $this->bxObject->getList($order = 'ID', $by = 'ASC', $filter, [
                'NAV_PARAMS' => [
                    'nTopCount' => 0,
                ],
            ])->NavRecordCount;
        };

        return $this->handleCacheIfNeeded(compact('queryType', 'filter'), $callback);
    }

    /**
     * Determine if groups must be selected.
     *
     * @return bool
     */
    protected function groupsMustBeSelected()
    {
        return in_array('GROUPS', $this->select) || in_array('GROUP_ID', $this->select) || in_array('GROUPS_ID', $this->select);
    }

    /**
     * Normalize filter before sending it to getList.
     * This prevents some inconsistency.
     *
     * @return array
     */
    protected function normalizeFilter()
    {
        $this->substituteField($this->filter, 'GROUPS', 'GROUPS_ID');
        $this->substituteField($this->filter, 'GROUP_ID', 'GROUPS_ID');

        return $this->filter;
    }

    /**
     * Normalize select before sending it to getList.
     * This prevents some inconsistency.
     *
     * @return array
     */
    protected function normalizeSelect()
    {
        if ($this->fieldsMustBeSelected()) {
            $this->select = array_merge($this->standardFields, $this->select);
        }

        $this->select[] = 'ID';

        return $this->clearSelectArray();
    }

    /**
     * Normalize select UF before sending it to getList.
     *
     * @return array
     */
    protected function normalizeUfSelect()
    {
        return preg_grep('/^(UF_+)/', $this->select);
    }
    
    protected function prepareMultiFilter(&$key, &$value)
    {
        $value = join(' | ', $value);
    }
}
