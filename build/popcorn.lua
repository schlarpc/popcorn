configuration=
{
	daemon=false,
	pathSeparator="/",

	logAppenders=
	{
		{
			name="console appender",
			type="coloredConsole",
			level=6
		}
	},
	
	applications=
	{
		rootDirectory="applications",
		{
			description="Popcorn stream",
			name="flvplayback",
			protocol="dynamiclinklibrary",
			default=true,
			acceptors = 
			{
				{
					ip="0.0.0.0",
					port=1935,
					protocol="inboundRtmp"
				}
			},
			validateHandshake=false,
			keyframeSeek=true,
			seekGranularity=1.5, --in seconds, between 0.1 and 600
			clientSideBuffer=0, --in seconds, between 5 and 30

		},
	}
}

