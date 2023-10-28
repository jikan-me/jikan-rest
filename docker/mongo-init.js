const userToCreate = fs.readFileSync('/run/secrets/db_username', 'utf8');
const userPassword = fs.readFileSync('/run/secrets/db_password', 'utf8');
db = db.getSiblingDB("admin");

db.createUser({
    user: userToCreate,
    pwd: userPassword,
    roles: [{ role: "readWrite", db: "jikan" }],
});

db = db.getSiblingDB("jikan");

db.createUser({
    user: userToCreate,
    pwd: userPassword,
    roles: [{ role: "readWrite", db: "jikan" }],
});
