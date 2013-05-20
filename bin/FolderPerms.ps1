#
# This snippet gets a dir from ther user, sets owner of thet folder and subfolders to the
# Administrators group and grants the user IUSR Full access and Users read recursively and enables inheritance.
#
#

echo ""
echo "=======Set Folder Perms======="
echo ""
echo ""
echo "Set Administrators as Owner, IUSR R/W and Users R"
echo ""


foreach ($i in $args)
{
    icacls "$i" /setowner administrators /t
    icacls "$i" /grant:r IUSR:"(OI)(CI)"F /t /inheritance:e
    icacls "$i" /grant:r Users:"(OI)(CI)"F /t /inheritance:e
}