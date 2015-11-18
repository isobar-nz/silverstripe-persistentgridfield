# silverstripe-persistentgridfield

Persistent `GridField` state across page reloads.

## Usage

```
$fields->addFieldToTab('Root.TeamMembers',
    PersistentGridField::create(
        'TeamMembers',
        'TeamMembers',
        TeamMember::get()
        ));
```
