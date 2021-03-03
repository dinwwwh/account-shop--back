# ACCOUNT SHOP VERSION API

- [Facebook](https://fb.com/dinhdjj)
- [Mail: dinhdjj@gmail.com](mailto:dinhdjj@gmail.com)

## Feature rule

Include infos to make rule for validate in front-end and back-end.

- Table: `rules`
- Model: `Rule`
- Controller: `RuleController`
- Resource: `RuleResource`

1. Create `done`
2. Show `done`
3. Update `done`
4. Destroy `done`
5. Middleware

## Feature publisher

Contain infos, action, require to account when publish in website.

- Table: `publishers`
- Model: `Publisher`
- Controller: `PublisherController`
- Resource: `PublisherResource`
- Relationship: `hasMany-AccountType`

1. Create `done`
2. Show `done`
3. Update `done`
4. Destroy `done`
5. Middleware

## Relationship: account type

Describe account type for account.

- Table: `account_types`
- Model: `AccountType`
- Controller: `AccountTypeController`
- Resource: `AccountTypeResource`
- Relationship: `belongTo-Publisher` `hasMany-AccountInfo` `hasMany-AccountAction`

1. Create
2. Show
3. Update
4. Destroy
5. Middleware

### Relationship: Infos

Contain necessary infos of a account type to user provide.

- Table: `account_infos`
- Model: `AccountInfo`
- Controller: `AccountInfoController`
- Resource: `AccountInfoResource`
- Relationship: `belongTo-AccountType`

1. Create
2. Show
3. Update
4. Destroy
5. Middleware

### Relationship: Actions

Contain necessary accounts of a account type to user implementation.

- Table: `account_actions`
- Model: `AccountAction`
- Controller: `AccountActionController`
- Resource: `AccountActionResource`
- Relationship: `belongTo-AccountType`

1. Create
2. Show
3. Update
4. Destroy
5. Middleware

## HTTP error States

- `422` failed validation
- `500` Sever error
