#! /usr/bin/perl -w
use strict;

my $cols = 2;

my $query2 = "
select count(u.pid)
from users as u
where printed = 1;
";

my $query = "
select u.pid, count(serial), count(distinct qorder,qsubord), max(time)-begintime, submitted from
users as u
left join events as e on u.pid = e.pid
where printed = 0
group by e.pid
order by submitted,time;
";

my $cmd = "mysql -N -u root demo";

print "Printed: ";
print `$cmd -e '$query2'`;

open IN,"$cmd -e '$query' |";
my $num = 0;
while (my $line = <IN>) {
	chomp $line;
	my @parts = split '\t', $line;
	if ($parts[4]) {
		print ">".$parts[0]."<";
	} else {
		print $parts[0];
	} 
	print " cas: ".$parts[3]." odp: ".$parts[2]."(".$parts[1].")";

	$num++;
	
	if ($num % $cols == 0) {
		print "\n";
	} else {
		print "\t";
	}
}
print "\n";
close IN;
